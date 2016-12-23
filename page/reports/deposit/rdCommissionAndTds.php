<?php

class page_reports_deposit_rdCommissionAndTds extends Page{
	public $title = 'Rd Commission and TDS Report';
	
	function init(){
		parent::init();

		$from_date = $this->api->today;
		$to_date = $this->api->nextDate($this->api->today);
		if($_GET['from_date'])
			$from_date = $_GET['from_date'];
		if($_GET['to_date'])
			$to_date = $this->api->nextDate($_GET['to_date']);

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->set($from_date);
		$form->addField('DatePicker','to_date')->set($this->api->previousDate($to_date));
		$form->addSubmit('Go');

		$transactions = $this->add('Model_Transaction');
		$transactions->addCondition('created_at','>=',$from_date);
		$transactions->addCondition('created_at','<',$to_date);
		
		$transaction_type_join = $transactions->join('transaction_types','transaction_type_id');
		$transaction_type_join->addField('transaction_type_name','name');

		$transactions->addCondition('transaction_type_name',TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT);

		$reference_account_join = $transactions->join('accounts','reference_id');
		$referecen_account_scheme_join = $reference_account_join->join('schemes','scheme_id');
		$referecen_account_scheme_join->addField('account_scheme','name');

		$transactions->addExpression('commission_amount')->set(function($m,$q){
			return $m->refSQL('TransactionRow')->addCondition('amountDr','<>',0)->fieldQuery('amountDr');
		});

		

		$grid= $this->add('Grid');
		$grid->setModel($transactions,array('reference','commission_amount','account_scheme'));

	}
}