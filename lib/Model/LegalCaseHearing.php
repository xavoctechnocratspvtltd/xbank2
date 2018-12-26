<?php

class Model_LegalCaseHearing extends Model_Table {
	public $table ="account_legal_case_hearing";

	function init(){
		parent::init();


		$this->hasOne('LegalCase','legalcase_id')->sortable(true);
		$this->addField('hearing_date')->type('date')->sortable(true);
		$this->addField('stage')->enum(LEGAL_CASE_STAGES)->defaultValue('Investigation')->sortable(true);
		$this->addField('remarks');

		$this->setOrder('hearing_date','desc');

		$this->addExpression('account_id')->set($this->refSQL('legalcase_id')->fieldQuery('account_id'));
		$this->addExpression('account')->set($this->refSQL('legalcase_id')->fieldQuery('account'));
		$this->addExpression('case_on')->set($this->refSQL('legalcase_id')->fieldQuery('case_on'));
		$this->addExpression('case_type')->set($this->refSQL('legalcase_id')->fieldQuery('case_type'));
		$this->addExpression('legal_filing_date')->set($this->refSQL('legalcase_id')->fieldQuery('legal_filing_date'));
		$this->addExpression('court')->set($this->refSQL('legalcase_id')->fieldQuery('court'));
		$this->addExpression('autorised_person')->set($this->refSQL('legalcase_id')->fieldQuery('autorised_person'));
		$this->addExpression('advocate')->set($this->refSQL('legalcase_id')->fieldQuery('advocate'));
		$this->addExpression('account_guarantor')->set($this->refSQL('legalcase_id')->fieldQuery('account_guarantor'));
		$this->addExpression('bccs_file_no')->set($this->refSQL('legalcase_id')->fieldQuery('bccs_file_no'));

		$this->addExpression('owner')->set(function ($m,$q){
			return $this->add('Model_Account')
						->addCondition('id',$m->getElement('account_id'))
						->fieldQuery('member_name_only');
		});

		$this->addExpression('dealer')->set(function ($m,$q){
			return $this->add('Model_Account')
						->addCondition('id',$m->getElement('account_id'))
						->fieldQuery('dealer');
		});
	}
}