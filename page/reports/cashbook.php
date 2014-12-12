<?php

class page_reports_cashbook extends Page {

	public $title="Cash Book";

	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addSubmit('Open Cash Book');

		$grid = $this->add('Grid_CashBook');

		$cash_transaction_model = $this->add('Model_Transaction');
		$transaction_row=$cash_transaction_model->join('transaction_row.transaction_id');
		$transaction_row->hasOne('Account','account_id');
		$transaction_row->addField('amountDr')->caption('Debit');
		$transaction_row->addField('amountCr')->caption('Credit');
		$account_join = $transaction_row->join('accounts','account_id');
		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('scheme_name','name');

		$cash_transaction_model->addCondition('scheme_name',CASH_ACCOUNT);
		$cash_transaction_model->add('Controller_Acl');

		if($_GET['from_date']){
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
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
			$grid->addOpeningBalance($opening_amount,$opening_column,array('Narration'=>$opening_narration),$opening_side);
			$grid->addCurrentBalanceInEachRow();
		}else{
			$cash_transaction_model->addCondition('id',-1);
		}

		$grid->setModel($cash_transaction_model,array('voucher_no','created_at','Narration','account','amountDr','amountCr'));
		$grid->addSno();

		// $grid->addTotals(array('amountCr','amountDr'));

		if($form->isSubmitted()){
			$grid->js()->reload(array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0))->execute();
		}
	}
}