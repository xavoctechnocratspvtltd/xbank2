<?php

class Model_MemberInsurance extends Model_Table {
	public $table='member_insurance';

	function init(){
		parent::init();

		$this->hasOne('ActiveMember','member_id')->mandatory(true)->display(array('form'=>'Member'))->mandatory(true);
		$this->hasOne('Account_Loan','accounts_id')->display(array('form'=>'autocomplete/Basic'))->mandatory(true);
		
		$this->addField('name')->caption('Insurance Number')->mandatory(true);
		$this->addField('insurance_start_date')->type('date')->mandatory(true);
		$this->addField('insurance_duration')->setValueList(['1'=>'1 Year','2'=>'2 Year','3'=>'3 Year','4'=>'4 Year','5'=>'5 Year','6'=>'6 Year','7'=>'7 Year','8'=>'8 Year','9'=>'9 Year','10'=>'10 Year'])->mandatory(true);
		$this->addField('narration')->type('text');
		
		$this->addField('next_insurance_due_date')->type('date')->system(true);
		$this->addField('is_renew')->type('boolean')->defaultValue(false);

		$this->addExpression('account_number')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('AccountNumber')]);
		});

		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		$loan_model = $this->add('Model_Account_Loan')->load($this['accounts_id']);
		$this['member_id'] = $loan_model['member_id'];
		$this['next_insurance_due_date'] = date('Y-m-d',strtotime("+".$this['insurance_duration']." year",strtotime($this['insurance_start_date'])));
	}

}