<?php
class Model_Dealer extends Model_Table {
	var $table= "dealers";
	function init(){
		parent::init();

		$this->hasOne('DSA','dsa_id');
		$this->addField('name')->caption('Firm Name');
		$this->addField('properitor_name');
		$this->addField('properitor_phone_no_1');
		$this->addField('properitor_phone_no_2');
		$this->addField('email_id_1');
		$this->addField('email_id_2');
		$this->addField('product');
		
		$this->addField('username')->mandatory(true);
		$this->addField('password')->mandatory(true);
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true);
		
		$this->addField('Address')->type('text');
		$this->addField('loan_panelty_per_day')->hint('Amount in rupees (int) no special symbol');
		$this->addField('time_over_charge')->hint('Amount in rupees (int) no special symbol');
		$this->addField('dealer_monthly_date')->hint('only date like 15 or 10 or 20');

		$this->hasMany('Account','dealer_id');

		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeSave',function($m){if(!$this['loan_panelty_per_day']) $this['loan_panelty_per_day'] = null;});


		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		if($this->ref('Account')->count()->getone() > 0)
			throw $this->exception('Dealer is used in Accounts');
		
	}

	function accounts(){
		return $this->ref('Account');
	}
}