<?php
class Model_Scheme extends Model_Table {
	var $table= "schemes";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->hasOne('BalanceSheet','balance_sheet_id');
		$this->addField('name');
		$this->addField('MinLimit')->caption('Minimum Balance/Amount');
		$this->addField('MaxLimit');
		$this->addField('Interest')->caption('Interest (In %)');
		$this->addField('InterestMode');
		$this->addField('InterestRateMode');
		$this->addField('LoanType');
		$this->addField('AccountOpenningCommission');
		$this->addField('Commission');
		$this->addField('ActiveStatus')->setValueList(array('InActive','Active'));
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('ProcessingFees');
		$this->addField('PostingMode');
		$this->addField('PremiumMode');
		$this->addField('CreateDefaultAccount');
		$this->addField('SchemeType')->enum(explode(',',ACCOUNT_TYPES));
		$this->addField('SchemeGroup');
		$this->addField('InterestToAnotherAccount')->type('boolean');
		$this->addField('NumberOfPremiums')->type('int');
		$this->addField('MaturityPeriod')->type('int');
		$this->addField('InterestToAnotherAccountPercent');
		$this->addField('isDepriciable')->type('boolean');
		$this->addField('DepriciationPercentBeforeSep');
		$this->addField('DepriciationPercentAfterSep');
		$this->addField('ProcessingFeesinPercent')->type('boolean')->defaultValue(false);
		$this->addField('published')->type('boolean')->defaultValue(true);
		$this->addField('SchemePoints');
		$this->addField('AgentSponsorCommission');
		$this->addField('CollectorCommissionRate');
		$this->addField('ReducingOrFlatRate');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function manageForm($form){
		if($form->isSubmitted())
			throw $this->exception('please redefine manageForm function in your scheme model without calling parent::manageForm');
			
	}
}