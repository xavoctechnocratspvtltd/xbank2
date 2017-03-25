<?php

class page_reports_deposit_fdProvision extends Page {
	public $title='FD Provision Report';

	function init(){
		parent::init();
		

		$on_date = $this->api->today;
		
		if($this->api->stickyGET('on_date'))
			$on_date = $_GET['on_date'];
		

		$form = $this->add('Form');
		$accounts_no_field=$form->addField('autocomplete/Basic','account_no');

		$accounts=$this->add('Model_Account');
		$accounts_no_field->setModel($accounts);

		$form->addField('DatePicker','on_date','For Month')->validateNotNull();
		$form->addSubmit('Go');

		$static_accounts = $this->add('Model_Account_FixedAndMis');
		$static_accounts->addCondition('ActiveStatus',true);
		$provisions= $static_accounts->provisions($this->api->monthFirstDate($on_date),$this->api->monthLastDate($on_date));

		if($this->api->stickyGET('account_no')){
			$provisions->addCondition('reference_id',$_GET['account_no']);
		}

		$provisions->add('Controller_Acl',array('branch_field'=>'account_braqnch_id'));

		$grid = $this->add('Grid_AccountsBase');

		$grid->add('H3',null,'grid_buttons')->set('FD Provision List For '. date('M-Y',strtotime($on_date)) );
		$grid->setModel($provisions,array('reference','scheme_name','Interest','account_created','amountDr','voucher_no'));

		$grid->addMethod('format_days',function($g,$f)use($on_date){
			if(strtotime($g->current_row['account_created']) < strtotime(date('01-m-Y',strtotime($on_date))))
				$g->current_row[$f] = date('t',strtotime($g->current_row['account_created']));
			else{
				// $g->current_row[$f]=date('01-mm-YY',strtotime($on_date));
				$g->current_row[$f] = date('t',strtotime($g->current_row['account_created'])) - date('d',strtotime($g->api->previousDate($g->current_row['account_created'])));
			}
		});

		$grid->addColumn('days','days');
		$grid->addFormatter('reference','wrap');
		$grid->addQuickSearch(array('reference'));
		// $grid->removeColumn('account_created');
		$grid->addSno();
		$grid->addPaginator(500);
		$grid->addTotals(['amountDr']);

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);
		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'on_date'=>$form['on_date']?:0,
					'to_date'=>$form['to_date']?:0,
					'account_no'=>$form['account_no']
				))->execute();
		}



	}
}