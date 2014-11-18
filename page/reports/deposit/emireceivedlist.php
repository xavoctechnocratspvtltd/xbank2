<?php

class page_reports_deposit_emireceivedlist extends Page {
	public $title="Deposit Premium Received List";
	function init(){
		parent::init();


		$till_date = $this->api->today;

		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','account_type')->setValueList(array('%'=>'All','Recurring'=>'RD','DDS'=>'DDS'));

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Deposit Premium Received List As On '. date('d-M-Y',strtotime($till_date))); 

		$transaction_row_model=$this->add('Model_TransactionRow');
		
		$transaction_join = $transaction_row_model->join('transactions','transaction_id');
		$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
		$account_join = $transaction_row_model->join('accounts','account_id');
		$account_member_join = $account_join->join('members','member_id');
		$dealer_join = $account_join->leftJoin('dealers','dealer_id');
		$scheme_join = $account_join->join('schemes','scheme_id');

		$dealer_join->addField('dealer_name','name');
		$account_member_join->addField('member_name','name');
		$account_member_join->addField('FatherName');
		$account_member_join->addField('phone_no','PhoneNos');

		$agent_join=$account_join->join('agents','agent_id');
		$agent_member_join = $agent_join->join('members','member_id');
		$agent_member_join->addField('agent_name','name');

		$agent_sb_join = $agent_join->join('accounts','account_id');
		$agent_sb_join->addField('agent_account_number','AccountNumber');
		$account_join->addField('AccountNumber');
		$account_join->addField('account_type');
		$account_join->addField('dealer_id');
		$scheme_join->addField('SchemeType');
		$transaction_type_join->addField('transaction_type_name','name');

		$transaction_row_model->addCondition('transaction_type_name',TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT);
		$transaction_row_model->addCondition('amountCr','>',0);
		$transaction_row_model->addCondition(
			$transaction_row_model->dsql()->orExpr()
				->where('SchemeType','Recurring')
				->where('SchemeType','DDS')
			);

		$transaction_row_model->getElement('amountCr')->caption('Amount Deposited');

		$transaction_row_model->setOrder('account_id desc,created_at desc');
		
		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("account_type");

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$transaction_row_model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$transaction_row_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

			if($_GET['account_type'])
				$transaction_row_model->addCondition('account_type','like',$_GET['account_type']);

		}else
			$transaction_row_model->addCondition('id',-1);

		$transaction_row_model->add('Controller_Acl');
		$transaction_row_model->setOrder('created_at','desc');

		$grid->setModel($transaction_row_model,array('AccountNumber','member_name','phone_no','FatherName','amountCr','agent_name','agent_account_number'));
		$grid->addPaginator(50);
		$grid->addSno();

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);

		if($form->isSubmitted()){
			$grid->js()->reload(array('account_type'=>$form['account_type'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}
	}
}