<?php

class page_reports_cashbook extends Page {

	public $title="Cash Book";

	function init(){
		parent::init();
		
		$this->api->stickyGET('from_date');
		$this->api->stickyGET('to_date');
		$form = $this->add('Form')->addClass('noneprintalbe');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();

		if($this->app->auth->model->isSuper()){
			$form->addField('DropDown','branch')->setEmptyText('All')->setModel('Branch');
		}

		$form->addSubmit('Open Cash Book');
		$grid = $this->add('Grid_CashBook');
		$grid->add('H3',null,'grid_buttons')->setHtml('Cash Book  <small class="atk-move-right"> From Date: '.$_GET['from_date']."&nbsp; &nbsp; &nbsp; To Date: ".$_GET['to_date']. '</small>' );	

		$cash_transaction_model = $this->add('Model_Transaction');
		$transaction_row=$cash_transaction_model->join('transaction_row.transaction_id');
		$transaction_row->hasOne('Account','account_id');
		$transaction_row->addField('amountDr')->caption('Debit');
		$transaction_row->addField('amountCr')->caption('Credit');
		$account_join = $transaction_row->join('accounts','account_id');
		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('scheme_name','name');

		// $ref_account = $cash_transaction_model->join('accounts','reference_id');
		// $ref_account_member = $ref_account->join('members','member_id');
		// $ref_account_member->addField('member_name','name');
		// $ref_account_member->addField('PermanentAddress');
		// $ref_account_member->addField('PanNo');

		$cash_transaction_model->addCondition('scheme_name',CASH_ACCOUNT);
		$cash_transaction_model->setOrder('voucher_no');
		$cash_transaction_model->add('Controller_Acl');

		if($_GET['from_date']){
			$cash_transaction_model->addCondition('created_at','>=',$_GET['from_date']);
			$cash_transaction_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			
			$cash_account = $this->add('Model_Account');
			$cash_account->addCondition('scheme_name',CASH_ACCOUNT_SCHEME);
			$cash_account->add('Controller_Acl');
			$cash_account->tryLoadAny();
			$opening_balance = $cash_account->getOpeningBalance($_GET['from_date']);

			if(($opening_balance['DR'] - $opening_balance['CR']) > 0){
				$opening_column = 'amountDr';
				$opening_amount = $opening_balance['DR'] - $opening_balance['CR'];
				$opening_narration = "To Opening balace";
				$opening_side = 'DR';
			}else{
				$opening_column = 'amountCr';
				$opening_amount = $opening_balance['CR'] - $opening_balance['DR'];
				$opening_narration = "By Opening balace";
				$opening_side = 'CR';
			}

			if($_GET['branch']){
				$cash_transaction_model->addCondition('branch_id',$_GET['branch']);
			}

			$grid->addOpeningBalance($opening_amount,$opening_column,array('Narration'=>$opening_narration),$opening_side);
			$grid->addCurrentBalanceInEachRow();
		}else{
			$cash_transaction_model->addCondition('id',-1);
		}

		$grid->setModel($cash_transaction_model,array('voucher_no','created_at','Narration','member_name','PermanentAddress','PanNo','amountDr','amountCr'));
		$grid->addSno();

		// $grid->addTotals(array('amountCr','amountDr'));

		if($form->isSubmitted()){
			$grid->js()->reload(array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'branch'=>$form['branch']?:0))->execute();
		}
	}
}