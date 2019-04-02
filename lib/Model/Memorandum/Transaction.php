<?php
class Model_Memorandum_Transaction extends Model_Table {
	var $table = "memorandum_transactions";

	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id')->display(['form'=>'autocomplete/Basic'])->defaultValue($this->app->current_staff->id)->system(true);
		$this->hasOne('Branch','branch_id')->display(['form'=>'autocomplete/Basic'])->defaultValue($this->app->current_branch->id)->system(true);
		// used for reference for memorandom transaction is done
		$this->hasOne('Account','reference_id')->display(['form'=>'autocomplete/Basic']);

		$this->addField('name'); //it may be invoice no
		$this->addField('transaction_type')->setValueList($this->getTransactionType())->mandatory(true); // visiting charge
		$this->addField('narration')->type('text');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);

		$this->hasMany('Memorandum_TransactionRow','memorandum_transaction_id');

		$this->addExpression('net_amount')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('amount');
		});

		$this->addExpression('tax_amount')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('tax_amount');
		});

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getTransactionType(){
		$type = [
			'Visiting Charge'=>'Visiting Charge',
			'Legal Charge'=>'Legal Charge',
			'Cheque Return Charge'=>'Cheque Return Charge',
			'File Cancel Charge'=>'File Cancel Charge',
			'Godown Charge'=>'Godown Charge',
			'Legal Expenses Receipt'=>'Legal Expenses Receipt',
			'Minimum Balance Charge Received on Saving'=>'Minimum Balance Charge Received on Saving',
			'Noc handling Charge'=>'Noc handling Charge',
			'Staff Stationary Charge Received'=>'Staff Stationary Charge Received',
			'Bike Auction Charge'=>'Bike Auction Charge',
			'Legal Notice Sent For Bike Auction'=>'Legal Notice Sent For Bike Auction',
			'Final Recovery Notice Sent'=>'Final Recovery Notice Sent',
			'Notice Sent After Cheque Return'=>'Notice Sent After Cheque Return',
			'Society Notice Sent'=>'Society Notice Sent'
		];

		return $type;
	}


}