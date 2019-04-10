<?php
class Model_Memorandum_TransactionRow extends Model_Table {
	var $table= "memorandum_transactionsrow";

	function init(){
		parent::init();

		$this->hasOne('Memorandum_Transaction','memorandum_transaction_id')->display(['form'=>'autocomplete/Basic']);
		$this->hasOne('Account','account_id')->display(['form'=>'autocomplete/Basic']);
		
		$this->addField('tax')->setValueList(GST_VALUES);
		$this->addField('tax_percentage');
		$this->addField('tax_amount');
		$this->addField('tax_narration')->type('text'); //saving sub tax value in json format
		$this->addField('amount_cr')->type('money'); // tax included amount
		$this->addField('amount_dr')->type('money'); // tax included amount

		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}