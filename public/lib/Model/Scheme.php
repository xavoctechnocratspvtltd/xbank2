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
		$this->addField('type');
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

		$this->addHook('beforeSave',array($this,'defaultBeforeSave'));
		$this->addHook('afterInsert',array($this,'defaultAfterInsert'));
		$this->addHook('beforeDelete',array($this,'defaultBeforeDelete'));


		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function defaultBeforeSave(){
		if(!$this['balance_sheet_id'])
			throw $this->exception('Head / Balance Sheet Id is must to define','ValidityCheck')->setField('balance_sheet_id');
		
		if($this->hasElement('ReducingOrFlatRate') and !$this['ReducingOrFlatRate'])
			throw $this->exception('Please Specify Interest Type', 'ValidityCheck')->setField('ReducingOrFlatRate');

		if($this->hasElement('PremiumMode') and !$this['PremiumMode'])
			throw $this->exception('Please Specify Premium Mode', 'ValidityCheck')->setField('PremiumMode');

		if($this->hasElement('type') and !$this['type'])
			throw $this->exception('Please Specify Type', 'ValidityCheck')->setField('type');

	}

	function defaultAfterInsert($model,$new_id){
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
		$this['type'] = $loanType_if_loan;


		unset($other_values['name']);
		unset($other_values['balance_sheet_id']);
		unset($other_values['SchemeType']);
		unset($other_values['SchemeGroup']);
		unset($other_values['type']);

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

	function defaultBeforeDelete(){
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

	function getOpeningBalance($on_date=null,$side='both',$forPandL=false,$branch=null) {

		if(!$this->loaded()) throw $this->exception('Scheme Must be Loaded');
		if(!$on_date) $on_date = '1970-01-02';
		if(!$this->loaded()) throw $this->exception('Model Must be loaded to get opening Balance','Logic');

		$transaction_row=$this->add('Model_TransactionRow');
		// $transaction_join=$transaction_row->join('transactions.id','transaction_id');
		// $transaction_join->addField('transaction_date','created_at');
		
		$account_join = $transaction_row->join('accounts','account_id');
		$account_join->addField('scheme_id');
		$account_join->addField('AccountActiveStatus','ActiveStatus');
		$account_join->addField('affectsBalanceSheet');

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('SchemeType');
		$scheme_join->addField('SchemeGroup');

		$transaction_row->addCondition('scheme_id',$this->id);
		$transaction_row->addCondition('created_at','<',$on_date);
		$transaction_row->addCondition('AccountActiveStatus',true);
		$transaction_row->addCondition('affectsBalanceSheet',false);
		if($branch)
			$transaction_row->addCondition('branch_id',$branch->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($on_date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');

		$result = $transaction_row->_dsql()->getHash();

		// Opening balance SUM Now
		
		$account = $this->add('Model_Account');
		$account->addCondition('scheme_id',$this->id);

		if($branch)
			$account->addCondition('branch_id',$branch->id);

		$account->_dsql()->del('fields')->field('SUM(OpeningBalanceCr) opcr')->field('SUM(OpeningBalanceDr) opdr');
		$result_op = $account->_dsql()->getHash();


		$cr = $result['scr'];
		if(!$forPandL) $cr = $cr + $result_op['opcr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if(!$forPandL) $dr = $dr + $result_op['opdr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}

	function getOpeningBalanceByGroup($on_date=null,$forPandL=false,$branch=null,$underHead, $groupByField='SchemeGroup') {

		// Only on Non Loaded Scheme
		if($this->loaded()) throw $this->exception('Scheme Must NOT be Loaded');
		if(!$on_date) $on_date = '1970-01-02';


		$transaction_row=$this->add('Model_TransactionRow');
		// $transaction_join=$transaction_row->join('transactions.id','transaction_id');
		// $transaction_join->addField('transaction_date','created_at');
		
		$account_join = $transaction_row->join('accounts','account_id');
		$account_join->addField('scheme_id');
		$account_join->addField('AccountActiveStatus','ActiveStatus');
		$account_join->addField('affectsBalanceSheet');

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('SchemeType');
		$scheme_join->addField('SchemeGroup');
		$scheme_join->addField('balance_sheet_id');

		$head_join = $scheme_join->join('balance_sheet','balance_sheet_id');
		$head_join->addField('subtract_from');

		$transaction_row->addCondition('created_at','<',$on_date);
		$transaction_row->addCondition('AccountActiveStatus',1);
		$transaction_row->addCondition('affectsBalanceSheet',0);
		$transaction_row->addCondition('balance_sheet_id',$underHead->id);

		if($branch)
			$transaction_row->addCondition('branch_id',$branch->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($on_date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		// SPECIAL GROUP BY CONDITION
		// $transaction_row->addCondition($groupByField,$groupBy);
		
		$transaction_row->_dsql()->del('fields')
			->field('SUM(amountDr) sDr')
			->field('SUM(amountCr) sCr')
			->field('(SUM(amountDr) - SUM(amountCr)) amt')
			->field('subtract_from')
			->field($groupByField)
			->group($groupByField);

		$results = $transaction_row->_dsql()->get();

		// Opening balance SUM Now
		
		$account = $this->add('Model_Account');
		$scheme_join = $account->join('schemes','scheme_id');
		$head_join = $scheme_join->join('balance_sheet','balance_sheet_id');

		$scheme_join->addField($groupByField);
		$head_join->addField('subtract_from');


		if($branch)
			$account->addCondition('branch_id',$branch->id);

		// SPECIAL GROUP BY CONDITION
		// $account->addCondition($groupByField,$groupBy);

		$account->_dsql()->del('fields')
			->field('SUM(OpeningBalanceCr) opCr')
			->field('SUM(OpeningBalanceDr) opDr')
			->field('subtract_from')
			->field($scheme_join->table_alias.'.'.$groupByField)
			->group($groupByField)
			;

		$results_op = $account->_dsql()->get();

		$return_array=array();

		foreach ($results as $r) {
			$_subtract_from = $r['subtract_from'];
			$_subtract_what = $r['subtract_from']=='Cr'?'Dr':'Cr';

			$amt_cr = $r['sCr'];
			$amt_dr = $r['sDr'];
			$amt = $r['s'.$_subtract_from] - $r['s'.$_subtract_what];

			foreach ($results_op as $a_o) {
				if($a_o['SchemeGroup'] == $r['SchemeGroup']){
					$op_amount = $a_o['op'.$_subtract_from] - $a_o['op'.$_subtract_what];
					$amt += $op_amount;
					$amt_cr += $a_o['opcr'];
					$amt_dr += $a_o['opdr'];
				}
			}



			if($amt != 0)
				$return_array[] = array('id'=>$r['SchemeGroup'],'SchemeGroup'=>$r['SchemeGroup'],'Amount'=>$amt/*. ($amt_dr > $amt_cr ? ' Dr':' Cr')*/);
		}
		return $return_array;

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