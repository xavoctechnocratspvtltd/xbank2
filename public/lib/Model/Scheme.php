<?php
class Model_Scheme extends Model_Table {
	var $table= "schemes";

	public $loanType=null;
	public $schemeType=null;

	function init(){
		parent::init();

		// if(!$this->loanType) throw $this->exception('Loan Type must be defined in Scheme Class')->addMoreInfo('Scheme',get_class())->addMoreInfo('loanType',$this->loanType===null?'n':'y');
		// if(!$this->schemeType) throw $this->exception('Scheme Type must be defined in Scheme Class');

		// $this->hasOne('Branch','branch_id')->defaultValue(@$this->api->current_branch->id);
		$this->hasOne('BalanceSheet','balance_sheet_id');
		$this->addField('name')->caption('Scheme Name')->mandatory(true);
		$this->addField('MinLimit')->caption('Minimum Balance/Amount');
		$this->addField('MaxLimit')->caption('Maximum Limit');
		$this->addField('Interest')->caption('Interest (In %)');
		$this->addField('InterestMode');
		$this->addField('InterestRateMode');
		$this->addField('LoanType')->type('boolean')->defaultValue($this->loanType);
		$this->addField('AccountOpenningCommission')->caption('Account Commissions(in %)');
		$this->addField('Commission');
		$this->addField('ActiveStatus')->type('boolean')->caption('Is Active');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('ProcessingFees')->caption('Processing Fees');
		$this->addField('PostingMode');
		$this->addField('PremiumMode')->setValueList(array(RECURRING_MODE_YEARLY=>'Yearly',RECURRING_MODE_HALFYEARLY=>'Half Yearly',RECURRING_MODE_QUATERLY=>'Quarterly',RECURRING_MODE_MONTHLY=>'Monthly',RECURRING_MODE_WEEKLY=>'Weekly',RECURRING_MODE_DAILY=>'Daily'))->mandatory(true);
		$this->addField('CreateDefaultAccount');
		$this->addField('SchemeType')->enum(explode(',',ACCOUNT_TYPES))->defaultValue($this->schemeType);
		$this->addField('SchemeGroup')->defaultValue($this->schemeType);
		$this->addField('InterestToAnotherAccount')->type('boolean');
		$this->addField('NumberOfPremiums')->type('int')->caption('Number Of Premiums');
		$this->addField('MaturityPeriod')->type('int');
		$this->addField('InterestToAnotherAccountPercent');
		$this->addField('isDepriciable')->type('boolean');
		$this->addField('DepriciationPercentBeforeSep')->caption('Depriciation % before September');
		$this->addField('DepriciationPercentAfterSep')->caption('Depriciation % after September');
		$this->addField('ProcessingFeesinPercent')->type('boolean')->defaultValue(false);
		$this->addField('published')->type('boolean')->defaultValue(true);
		$this->addField('SchemePoints')->caption('Scheme Points');
		$this->addField('AgentSponsorCommission');
		$this->addField('CollectorCommissionRate');
		$this->addField('ReducingOrFlatRate')->caption('Reducing Or Flat Rate')->enum(array('Flat','Reducing'))->mandatory(true);

		$this->hasMany('Account','scheme_id');

		$this->addHook('beforeSave',$this);
		$this->addHook('afterInsert',$this);


		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if(!$this['balance_sheet_id'])
			throw $this->exception('Head / Balance Sheet Id is must to define','ValidityCheck')->setField('balance_sheet_id');
		
	}

	function afterInsert($model,$new_id){
		$new_scheme = $this->add('Model_Scheme_'.$model['SchemeType'])->load($new_id);
		$all_branches = $this->add('Model_Branch');
		foreach ($all_branches as $branch_array) {
			$new_scheme->createDefaultAccounts($all_branches);
		}
	}

	// function manageForm($form){
	// 	if($form->hasElement('balance_sheet_id'))
	// 		$form->getElement('balance_sheet_id')->setEmptyText('Please Select ...');
	// 	if($form->hasElement('PremiumMode'))
	// 		$form->getElement('PremiumMode')->setEmptyText('Please Select ...');
	// 	if($form->hasElement('ReducingOrFlatRate'))
	// 		$form->getElement('ReducingOrFlatRate')->setEmptyText('Please Select ...');
	// 	$this->hook('schemeFormCreated',array($form));
	// 	if($form->isSubmitted()){
	// 		$this->hook('schemeFormSubmitted',array($form));
	// 		$values = $form->getAllFields();
	// 		$this->createNewScheme($values);
	// 		$form->js()->univ()->successMessage('HI')->execute();
	// 	}
	// }

	function getDefaultAccounts(){
		throw $this->exception('RE Declare The function in Specific Scheme Models');

		// if(!$this->schemeType) throw $this->exception('DefaultAccounts must be called from Specific Scheme Model not generic scheme Model');
		// $default_account_model = $this->add('Model_DefaultAccounts');
		// $default_account_model->filterFor($this->schemeType);
		// return $default_account_model->getAll();
	}

	// Overrides by Child Classes to add values and called as parent::...
	function createNewScheme($values=array()){
		foreach ($values as $field => $value) {
			$this[$field] = $value;
		}

		$this->save();

		// TODO - Create Default Accounts for this Scheme for all Branches
		// Foreach Branch Foreach $this->getDefaultAccounts ...
		$all_branches = $this->add('Model_Branch');
		foreach ($all_branches as $branch_array) {
			$this->createDefaultAccounts($all_branches);
		}
	}

	function createDefaultAccounts($branch){
		if(!$this->loaded()) throw $this->exception('Scheme Must be loaded to create default accounts for');
		if(!($branch instanceof Model_Branch) and !$branch->loaded()) throw $this->exception('Argument Branch must be a loaded Branch Model');


		foreach ($this->getDefaultAccounts() as $under_scheme => $intermediate_text) {

			$scheme = $this->add('Model_Scheme')->loadBy('name',$under_scheme);
			$account = $this->add('Model_Account');
			$account->createNewAccount($branch->getDefaultMember()->get('id'),$scheme->id,$branch,$branch['Code'].SP.$intermediate_text.SP.$this['name'],array('DefaultAC'=>true));

		}

	}

	function daily(){
		throw $this->exception('Daily closing function must be in scheme');
	}
	function monthly(){
		throw $this->exception('Monthly closing function must be in scheme');
	}
	function halfYearly(){
		throw $this->exception('Half Yearly closing function must be in scheme');
	}
	function yearly(){
		throw $this->exception('Yearly closing function must be in scheme');
	}

}