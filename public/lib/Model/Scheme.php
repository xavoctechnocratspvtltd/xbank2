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
		$this->addField('MinLimit')->caption('Minimum Balance/Amount')->type('int')->mandatory(true);
		$this->addField('MaxLimit')->caption('Maximum Limit');
		$this->addField('Interest')->caption('Interest (In %)')->type('money');
		$this->addField('InterestMode');
		$this->addField('InterestRateMode');
		$this->addField('type')->enum(array('Two Wheeler Loan','Auto Loan','Personal Loan','Loan Againest Deposit','Home Loan','Mortgage Loan','Agriculture Loan','Education Loan','Gold Loan','Other'));
		$this->addField('AccountOpenningCommission')->caption('Account Commissions(in %)');
		$this->addField('Commission');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true)->caption('Is Active')->system(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('ProcessingFees')->caption('Processing Fees');
		$this->addField('PostingMode');
		$this->addField('PremiumMode')->setValueList(array(RECURRING_MODE_YEARLY=>'Yearly',RECURRING_MODE_HALFYEARLY=>'Half Yearly',RECURRING_MODE_QUATERLY=>'Quarterly',RECURRING_MODE_MONTHLY=>'Monthly',RECURRING_MODE_WEEKLY=>'Weekly',RECURRING_MODE_DAILY=>'Daily'));
		$this->addField('CreateDefaultAccount');
		$this->addField('SchemeType')->enum(explode(',',ACCOUNT_TYPES))->defaultValue($this->schemeType);
		$this->addField('InterestToAnotherAccount')->type('boolean');
		$this->addField('NumberOfPremiums')->mandatory(true)->type('int')->caption('Number Of Premiums');
		$this->addField('MaturityPeriod')->type('int');
		$this->addField('InterestToAnotherAccountPercent');
		$this->addField('isDepriciable')->type('boolean');
		$this->addField('DepriciationPercentBeforeSep')->caption('Depriciation % before September');
		$this->addField('DepriciationPercentAfterSep')->caption('Depriciation % after September');
		$this->addField('ProcessingFeesinPercent')->type('boolean')->defaultValue(false);
		$this->addField('published')->type('boolean')->defaultValue(true);
		
		$this->addField('SchemePoints')->caption('Scheme Points')->system(true);
		$this->addField('SchemeGroup')->defaultValue($this->schemeType)->system(true);
		
		$this->addField('AgentSponsorCommission');
		$this->addField('CollectorCommissionRate');
		$this->addField('ReducingOrFlatRate')->caption('Interest Type')->enum(array('Flat','Reducing'));

		$this->hasMany('Account','scheme_id');

		$this->addHook('beforeSave',$this);
		$this->addHook('afterInsert',$this);
		$this->addHook('beforeDelete',$this);


		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if(!$this['balance_sheet_id'])
			throw $this->exception('Head / Balance Sheet Id is must to define','ValidityCheck')->setField('balance_sheet_id');
		
		if($this->hasElement('ReducingOrFlatRate') and !$this['ReducingOrFlatRate'])
			throw $this->exception('Please Specify Interest Type', 'ValidityCheck')->setField('ReducingOrFlatRate');

		if($this->hasElement('PremiumMode') and !$this['PremiumMode'])
			throw $this->exception('Please Specify Premium Mode', 'ValidityCheck')->setField('PremiumMode');

		if($this->hasElement('type') and !$this['type'])
			throw $this->exception('Please Specify Type', 'ValidityCheck')->setField('type');

	}

	function afterInsert($model,$new_id){
	}

	function getDefaultAccounts(){
		throw $this->exception('RE Declare The function in Specific Scheme Models');
	}

	// Overrides by Child Classes to add values and called as parent::...
	function createNewScheme($name,$balance_sheet_id, $scheme_type, $scheme_group, $loanType_if_loan, $other_values=array(),$form=null,$on_date=null){
		
		$this['name'] = $name;
		$this['balance_sheet_id'] = $balance_sheet_id;
		$this['SchemeType'] = $scheme_type;
		$this['SchemeGroup'] = $scheme_group;
		$this['LoanType'] = $loanType_if_loan;


		unset($other_values['name']);
		unset($other_values['balance_sheet_id']);
		unset($other_values['SchemeType']);
		unset($other_values['SchemeGroup']);
		unset($other_values['loanType']);

		foreach ($other_values as $field => $value) {
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

		$scheme = $this->add('Model_Scheme');
		$account = $this->add('Model_Account');
		foreach ($this->getDefaultAccounts() as $details) {
			$scheme->loadBy('name',$details['under_scheme']);
			$account->createNewAccount($branch->getDefaultMember()->get('id'),$scheme->id,$branch,$branch['Code'].SP.$details['intermediate_text'].SP.$this['name'],array('DefaultAC'=>true,'Group'=>$details['Group'],'PAndLGroup'=>$details['PAndLGroup']));
			$scheme->unload();
			$account->unload();
		}

	}

	function getNewSMAccountNumber(){
		$accounts = $this->add('Model_Account');
		$accounts->addCondition('scheme_name',CAPITAL_ACCOUNT_SCHEME);
		$accounts->addCondition('DefaultAC',false);
		$accounts->setOrder('id','desc');
		$accounts->tryLoadAny();


		preg_match_all("!\d+!", $accounts['AccountNumber'],$result);

		return "SM".($result[0][0]+1);

		$query = "select count(a.AccountNumber) from jos_xaccounts a join jos_xschemes s on a.schemes_id=s.id where a.DefaultAC = 0 and s.Name ='" . CAPITAL_ACCOUNT_SCHEME . "' ";
                    $accnum = getNextCode($com_params->get("default_share_accountnumber"), $query);
		return 0;
	}

	function prepareDelete(){
		foreach ($b=$this->add('Model_Branch') as $b_array) {
			$this->deleteDefaultAccounts($b);
			$this->deleteAccounts($b);
		}
	}

	function deleteDefaultAccounts($branch){
		foreach ($this->getDefaultAccounts() as $details) {
			$account = $this->add('Model_Account');
			$account->addCondition('AccountNumber',$branch['Code'].SP.$details['intermediate_text'].SP.$this['name']);
			foreach ($account as $acc_array) {
				$account->prepareDelete($revert_accounts_balances=true);
				$account->delete();
			}
		}
	}

	function deleteAccounts($branch){
		$accounts = $this->add('Model_Account');
		$accounts->addCondition('branch_id',$branch->id);
		$accounts->addCondition('scheme_id',$this->id);
		foreach ($accounts as $acc_array) {
			$accounts->prepareDelete($revert_accounts_balances=true);
			$accounts->delete();
		}
	}

	function beforeDelete(){
		foreach ($this->getDefaultAccounts() as $details) {
			$account = $this->add('Model_Account');
			$account->addCondition('AccountNumber','like','%'.$details['intermediate_text'].SP.$this['name']);
			$account->tryLoadAny();
			if($account->loaded()) throw $this->exception('Scheme Contains Default Accounts, Cannot Delete');
		}

		$scheme_accounts = $this->add('Model_Account');
		$scheme_accounts->addCondition('scheme_id',$this->id);
		$scheme_accounts->tryLoadAny();
		if($scheme_accounts->loaded()) throw $this->exception('Scheme Contains Accounts created under this, cannot delete');
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