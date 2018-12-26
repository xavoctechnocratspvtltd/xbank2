<?php

class Model_LegalCase extends Model_Table {
	public $table = "account_legal_case";

	function init(){
		parent::init();

		$this->hasOne('Account','account_id')->display(['form'=>'autocomplete/Basic']);
		$this->addField('name')->caption('Legal Case No');
		$this->addField('bccs_file_no');
		$this->addField('court');
		$this->addField('autorised_person');
		$this->addField('case_type')->enum(LEGAL_CASE_TYPES);
		$this->addField('stage')->enum(LEGAL_CASE_STAGES)->defaultValue('Investigation');
		$this->addField('case_on')->enum(['Owner','Guarantor']);
		$this->addField('file_verified_by');
		$this->addField('advocate');
		$this->addField('remarks');

		$this->hasMany('LegalCaseHearing','legalcase_id');

		$this->addExpression('legal_filing_date')->set(function($m,$q){
			return $m->refSQL('account_id')->fieldQuery('legal_filing_date');
		});

		$this->addExpression('last_hearing_date')->set(function($m,$q){
			return $m->refSQL('LegalCaseHearing')->setLimit(1)->setOrder('hearing_date','desc')->fieldQuery('hearing_date');
		});

		$this->addExpression('owner')->set(function ($m,$q){
			return $this->add('Model_Account')
						->addCondition('id',$m->getElement('account_id'))
						->fieldQuery('member_name_only');
		});

		$this->addExpression('account_guarantor')->set(function($m,$q){
			$ag = $this->add('Model_AccountGuarantor');
			$ag->addCondition('account_id',$m->getElement('account_id'));
			$ag->setLimit(1);
			return $ag->fieldQuery('member');
		});

		$this->addHook('beforeDelete',$this);
	}

	function beforeDelete(){
		if($this->ref('LegalCaseHearing')->count()->getOne() > 0 )
			throw new \Exception("Must remove hearing recoreds first", 1);
			
	}

}