<?php

class page_reports_deposit_fdinterestprovision extends Page {

	public $title="FD Interest Provision";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$account_field=$form->addField('autocomplete/Basic','account_no');
		$account_field->setModel('Account');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET List');
		
		$grid=$this->add('Grid_AccountsBase');

		$transaction_row_model=$this->add('Model_TransactionRow');
		$transaction_join = $transaction_row_model->join('transactions','transaction_id');
		$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
		$ref_account_join = $transaction_row_model->join('accounts','reference_id');
		$ref_account_scheme_join = $ref_account_join->join('schemes','scheme_id');

		$transaction_type_join->addField('transaction_name','name');
		$ref_account_join->addField('AccountNumber');
		$ref_account_join->addField('Amount');
		$ref_account_scheme_join->addField('scheme_name','name');

		$transaction_row_model->addCondition('transaction_name',TRA_INTEREST_PROVISION_IN_FIXED_ACCOUNT);

		if($_GET['filter']){
			$this->api->stickyGET("filter");
			if($_GET['account_no']){
				$acc=$this->add('Model_Account')->load($_GET['account_no']);
				$transaction_row_model->addCondition('AccountNumber',$acc['AccountNumber']);
			}
			
			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$transaction_row_model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$transaction_row_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

		}else
			$transaction_row_model->addCondition('id',-1);

		$transaction_row_model->setOrder('created_at','desc');

		$grid->setModel($transaction_row_model,array('AccountNumber','scheme_name','Amount','amountCr','created_at'));
		$grid->addPaginator(50);
		// $grid->addFormatter('voucher_no','voucherNo');
		// $grid->removeColumn('voucherNo');
		$grid->addSno();
		if($form->isSubmitted()){
			$grid->js()->reload(array('account_no'=>$form['account_no'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}
	}
}