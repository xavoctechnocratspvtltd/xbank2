<?php
class Model_Dealer extends Model_Table {
	var $table= "dealers";
	function init(){
		parent::init();

		$this->hasOne('DSA','dsa_id');
		$this->addField('name');
		$this->addField('Address')->type('text');
		$this->addField('loan_panelty_per_day')->hint('Amount in rupees (int) no special symbol');
		$this->addField('time_over_charge')->hint('Amount in rupees (int) no special symbol');
		$this->addField('dealer_monthly_date')->hint('only date like 15 or 10 or 20');

		$this->addHook('beforeDelete',$this);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		throw new Exception("Hook ... ????", 1);
		
	}
}