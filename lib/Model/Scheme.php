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
		$this->hasOne('BalanceSheet','balance_sheet_id')->sortable(true);
		$this->addField('name')->caption('Scheme Name')->mandatory(true)->sortable(true);
		$this->addField('MinLimit')->caption('Minimum Balance/Amount')->type('int')->mandatory(true);
		$this->addField('MaxLimit')->caption('Maximum Limit');
		$this->addField('Interest')->caption('Interest (In %)')->type('money')->sortable(true);
		$this->addField('InterestMode');
		$this->addField('InterestRateMode');
		$this->addField('type')->sortable(true);
		$this->addField('AccountOpenningCommission')->caption('Commissions(%)');
		$this->addField('Commission');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true)->caption('Is Active');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('ProcessingFees')->caption('Processing Fees')->sortable(true);
		$this->addField('ProcessingFeesinPercent')->type('boolean')->defaultValue(false);
		$this->addField('PostingMode');
		$this->addField('PremiumMode')->setValueList(array(RECURRING_MODE_YEARLY=>'Yearly',RECURRING_MODE_HALFYEARLY=>'Half Yearly',RECURRING_MODE_QUATERLY=>'Quarterly',RECURRING_MODE_MONTHLY=>'Monthly',RECURRING_MODE_WEEKLY=>'Weekly',RECURRING_MODE_DAILY=>'Daily'));
		$this->addField('CreateDefaultAccount');
		$this->addField('SchemeType')->enum(explode(',',ACCOUNT_TYPES))->defaultValue($this->schemeType);
		$this->addField('InterestToAnotherAccount')->type('boolean');
		$this->addField('NumberOfPremiums')->mandatory(true)->type('int')->caption('Number Of Premiums');
		$this->addField('MaturityPeriod')->type('int')->sortable(true);
		$this->addField('InterestToAnotherAccountPercent');
		$this->addField('isDepriciable')->type('boolean')->defaultValue(false)->sortable(true);
		$this->addField('DepriciationPercentBeforeSep')->caption('Depriciation % before September');
		$this->addField('DepriciationPercentAfterSep')->caption('Depriciation % after September');
		$this->addExpression('published')->set('ActiveStatus');
		$this->addField('percent_loan_on_deposit')->type('number')->defaultValue('80')->sortable(true);
		$this->addField('no_loan_on_deposit_till')->type('number')->defaultValue('0')->sortable(true);
		$this->addField('pre_mature_interests');
		$this->addField('mature_interests_for_uncomplete_product')->hint('in %')->type('money');
		$this->addField('valid_till')->type('date');

		
		$this->addField('SchemePoints')->caption('Scheme Points')->system(true);
		$this->addField('SchemeGroup')->defaultValue($this->schemeType);//->system(true);
		
		$this->addField('CRPB')->type('int');//->system(true);
		
		$this->addField('AgentSponsorCommission');
		$this->addField('CollectorCommissionRate');
		$this->addField('ReducingOrFlatRate')->caption('Interest Type')->enum(array('Flat','Reducing'))->defaultValue('Flat');

		$this->addField('panelty');
		$this->addField('panelty_grace');

		$this->hasMany('Account','scheme_id');
		$this->hasMany('TransactionRow','transaction_row_id');

		$this->addExpression('total_accounts')->set($this->refSQL('Account')->count())->caption('Accounts')->sortable(true);
		$this->addExpression('total_active_accounts')->set($this->refSQL('Account')->addCondition('ActiveStatus',true)->count())->caption('Active Accounts')->sortable(true);


		$this->addHook('editing',array($this,'defaultEditing'));
		$this->addHook('beforeSave',array($this,'defaultBeforeSave'));
		$this->addHook('beforeSave',array($this,'updateTransactionRows'));
		$this->addHook('afterInsert',array($this,'defaultAfterInsert'));
		$this->addHook('beforeDelete',array($this,'defaultBeforeDelete'));

		$this->add('Controller_Validator');
		$this->is(array(
							'name|to_trim|unique'
						)
				);


		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function putValidDateCondition(){
		$this->addCondition([['valid_till',null],['valid_till','>',$this->app->today]]);
		return $this;
	}

	function updateTransactionRows(){
		if($this->isDirty('balance_sheet_id') && $this->loaded()){
			$old = $this->add('Model_Scheme')->load($this->id);
			$tr = $this->add('Model_TransactionRow');
			$tr->addCondition('account_id',$old['balance_sheet_id']);
			$tr->addCondition('scheme_id',$this->id);

			$tr->set('balance_sheet_id',$this['balance_sheet_id']);
			$tr->_dsql()->update();
		}
	}

	function defaultEditing(){
		// $this->getElement('name')->display(array('form'=>'Readonly'));
		$this->getElement('type')->system(true);
		if($this->hasElement('MaturityPeriod')) $this->getElement('MaturityPeriod')->system(true);
		if($this->hasElement('Interest')) $this->getElement('Interest')->system(true);
		if($this->hasElement('InterestMode')) $this->getElement('InterestMode')->system(true);
		if($this->hasElement('InterestRateMode')) $this->getElement('InterestRateMode')->system(true);
		if($this->hasElement('AccountOpenningCommission')) $this->getElement('AccountOpenningCommission')->system(true);
		if($this->hasElement('CollectorCommissionRate')) $this->getElement('CollectorCommissionRate')->system(true);
		if($this->hasElement('Commission')) $this->getElement('Commission')->system(true);
		if($this->hasElement('ProcessingFees')) $this->getElement('ProcessingFees')->system(true);
		if($this->hasElement('PremiumMode')) $this->getElement('PremiumMode')->system(true);
		if($this->hasElement('SchemeType')) $this->getElement('SchemeType')->system(true);
		if($this->hasElement('InterestToAnotherAccount')) $this->getElement('InterestToAnotherAccount')->system(true);
		if($this->hasElement('NumberOfPremiums')) $this->getElement('NumberOfPremiums')->system(true);
		if($this->hasElement('InterestToAnotherAccountPercent')) $this->getElement('InterestToAnotherAccountPercent')->system(true);
		// if($this->hasElement('isDepriciable')) $this->getElement('isDepriciable')->system(true);
		// if($this->hasElement('DepriciationPercentBeforeSep')) $this->getElement('DepriciationPercentBeforeSep')->system(true);
		// if($this->hasElement('DepriciationPercentAfterSep')) $this->getElement('DepriciationPercentAfterSep')->system(true);
		if($this->hasElement('ProcessingFeesinPercent')) $this->getElement('ProcessingFeesinPercent')->system(true);
		if($this->hasElement('ReducingOrFlatRate')) $this->getElement('ReducingOrFlatRate')->system(true);
		if($this->hasElement('CRPB')) $this->getElement('CRPB')->system(true);
		
		// if($this->hasElement('MinLimit')) $this->getElement('MinLimit')->system(true);
		// if($this->hasElement('MaxLimit')) $this->getElement('MaxLimit')->system(true);
		
		// TEMPALLOW
		// $this->getElement('type')->system(false)->defaultValue('DDS');
		// if($this->hasElement('AccountOpenningCommission')) $this->getElement('AccountOpenningCommission')->system(false);
	}

	function defaultBeforeSave(){
		// Name change handel
		if($this->loaded()){
			if($this->dirty['name']){
				$old_name = $this->newInstance()->load($this->id)->get('name');

				$existing_check = $this->newInstance();
				$existing_check->addCondition('name',$this['name']);
				$existing_check->addCondition('id','<>',$this->id);
				$existing_check->tryLoadAny();

				if($existing_check->loaded())
					throw $this->exception('Scheme Name Already Used','ValidityCheck')->setField('name');

				$all_branches = $this->add('Model_Branch');
				foreach ($all_branches as $branch_array) {
					foreach ($this->getDefaultAccounts() as $details) {
						$account = $this->add('Model_Account');
						$account->loadBy('AccountNumber',$all_branches['Code'].SP.$details['intermediate_text'].SP.$old_name);
						$account['AccountNumber'] = $all_branches['Code'].SP.$details['intermediate_text'].SP.$this['name'];
						$account->saveAndUnload();
					}
				}
			}
		}

	}

	function defaultAfterInsert($model,$new_id){
	}

	function getDefaultAccounts(){
		throw $this->exception('Re Declare The function in Specific Scheme Models');
	}

	// Overrides by Child Classes to add values and called as parent::...
	function createNewScheme($name,$balance_sheet_id, $scheme_type, $scheme_group, $acc_type, $other_values=array(),$form=null,$on_date=null){
		
		$this['name'] = $name;
		$this['balance_sheet_id'] = $balance_sheet_id;
		$this['SchemeType'] = $scheme_type;
		$this['SchemeGroup'] = $scheme_group;
		$this['type'] = $acc_type;

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
		$scheme_accounts = $this->add('Model_Account');
		$scheme_accounts->addCondition('scheme_id',$this->id);
		$scheme_accounts->tryLoadAny();
		if($scheme_accounts->loaded()) throw $this->exception('Scheme Contains Accounts created under this, cannot delete');
		
		$scheme=$this->add('Model_Scheme_'.$this['SchemeType'])->load($this->id);
		foreach ($scheme->getDefaultAccounts() as $details) {
			$account = $this->add('Model_Account');
			$account->addCondition('AccountNumber','like','%'.$details['intermediate_text'].SP.$this['name']);

			foreach ($account as $junk) {
					$account->delete();
			}
		}

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
		$aj_ta = $account_join->table_alias;

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('SchemeType');
		$scheme_join->addField('SchemeGroup');
		$sj_ta=$scheme_join->table_alias;


		$transaction_row->addCondition('scheme_id',$this->id);
		$transaction_row->addCondition('created_at','<',$on_date);
		// $transaction_row->addCondition('AccountActiveStatus',true);
		// $transaction_row->addCondition('affectsBalanceSheet',false);
		if($branch)
			$transaction_row->addCondition('branch_id',$branch->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($on_date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');
		// $transaction_row->_dsql()->where("($aj_ta.ActiveStatus=1/* OR $aj_ta.affectsBalanceSheet=1*/)");

		$result = $transaction_row->_dsql()->getHash();

		// Opening balance SUM Now
		
		$account = $this->add('Model_Account',array('table_alias'=>'accounts'));
		$account->addCondition('scheme_id',$this->id);

		if($branch)
			$account->addCondition('branch_id',$branch->id);

		$account->_dsql()->del('fields')->field('SUM(OpeningBalanceCr) opcr')->field('SUM(OpeningBalanceDr) opdr');
		$aj_ta = $account->table_alias;
		// $account->_dsql()->where("($aj_ta.ActiveStatus=1 /*OR $aj_ta.affectsBalanceSheet=1*/)");
		$result_op = $account->_dsql()->getHash();


		$cr = $result['scr'];
		if(!$forPandL) $cr = $cr + $result_op['opcr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if(!$forPandL) $dr = $dr + $result_op['opdr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}

	function getOpeningBalanceByGroup($on_date=null,$forPandL=false,$branch=null,$underHead=null, $groupByField='SchemeGroup',$filter_by_schemes=null, $from_date=null) {

		if(!is_array($groupByField) or count($groupByField)!=3)
			throw $this->exception('groupByField should be array(field, from,new_name)', 'ValidityCheck')->setField('FieldName');

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
		$aj_ta = $account_join->table_alias;

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('SchemeType');
		$scheme_join->addField('SchemeGroup');
		$scheme_join->addField('balance_sheet_id');
		$sj_ta = $scheme_join->table_alias;

		$head_join = $scheme_join->join('balance_sheet','balance_sheet_id');
		$head_join->addField('subtract_from');
		$hj_ta = $head_join->table_alias;

		$transaction_row->addCondition('created_at','<',$on_date);
		if($underHead)
			$transaction_row->addCondition('balance_sheet_id',$underHead->id);
		// $transaction_row->_dsql()->where("($aj_ta.ActiveStatus=1 /*OR $aj_ta.affectsBalanceSheet=1*/)");

		if($branch)
			$transaction_row->addCondition('branch_id',$branch->id);

		if($forPandL){
			if(!$from_date) $from_date = $this->api->getFinancialYear($on_date,'start');
			$transaction_row->addCondition('created_at','>=',$from_date);
		}

		if($filter_by_schemes)
			$transaction_row->addCondition('scheme_id',$filter_by_schemes->id);

		// SPECIAL GROUP BY CONDITION
		// $transaction_row->addCondition($groupByField,$groupBy);
		
		if(is_array($groupByField)){
			$saved_group_by= $groupByField;
			switch (strtolower($groupByField[1])) {
				case 'account':
				case 'accounts':
					$groupByField = $aj_ta.'.'.$groupByField[0];
					break;
				case 'scheme':
				case 'schemes':
					$groupByField = $sj_ta.'.'.$groupByField[0];
					break;
				case 'head':
				case 'bs':
				case 'balancesheet':
				case 'balance_sheet':
					$groupByField = $hj_ta.'.'.$groupByField[0];
					break;
				
				default:
					$groupByField = $groupByField[0];
					break;
			}
		}

		$transaction_row->_dsql()->del('fields')
			->field('SUM(amountDr) sDr')
			->field('SUM(amountCr) sCr')
			->field('(SUM(amountDr) - SUM(amountCr)) amt')
			->field('subtract_from')
			->field($groupByField . ' ' .$saved_group_by[2])
			->group($groupByField);

		$results = $transaction_row->_dsql()->get();

		// Opening balance SUM Now
		
		$account = $this->add('Model_Account',array('table_alias'=>'accounts'));
		$scheme_join = $account->scheme_join;
		$head_join = $account->scheme_join->join('balance_sheet','balance_sheet_id');

		if(!$account->hasElement($groupByField) and $account->scheme_join->hasElement($groupByField))
			$account->scheme_join->addField($groupByField);
		
		$head_join->addField('subtract_from');


		if($branch)
			$account->addCondition('branch_id',$branch->id);

		if($filter_by_schemes)
			$account->addCondition('scheme_id',$filter_by_schemes->id);

		// SPECIAL GROUP BY CONDITION
		// $account->addCondition($groupByField,$groupBy);

		$groupByField = $saved_group_by;
		if(is_array($groupByField)){
			$saved_group_by= $groupByField;
			switch (strtolower($groupByField[1])) {
				case 'account':
				case 'accounts':
					$groupByField = $account->table_alias . '.'. $groupByField[0];
					break;
				case 'scheme':
				case 'schemes':
					$groupByField = $sj_ta.'.'.$groupByField[0];
					break;
				case 'head':
				case 'bs':
				case 'balancesheet':
				case 'balance_sheet':
					$groupByField = $hj_ta.'.'.$groupByField[0];
					break;
				
				default:
					$groupByField = $groupByField[0];
					break;
			}
		}

		$account->_dsql()->del('fields')
			->field('SUM(OpeningBalanceCr) opCr')
			->field('SUM(OpeningBalanceDr) opDr')
			->field('subtract_from')
			->field($groupByField .' '. $saved_group_by[2])
			->group($groupByField)
			;

		$results_op = $account->_dsql()->get();

		$return_array=array();

		// if(is_array($groupByField)) $groupByField = $groupByField[1]; 
		$groupByField=$saved_group_by;

		foreach ($results as $r) {
			$_subtract_from = $r['subtract_from'];
			$_subtract_what = $r['subtract_from']=='Cr'?'Dr':'Cr';

			$amt_cr = $r['sCr'];
			$amt_dr = $r['sDr'];
			$amt = $r['s'.$_subtract_from] - $r['s'.$_subtract_what];

			foreach ($results_op as $a_o) {
				if($a_o[$groupByField[2]] == $r[$groupByField[2]]){
					$op_amount = $a_o['op'.$_subtract_from] - $a_o['op'.$_subtract_what];
					$amt += $op_amount;
					$amt_cr += $a_o['opcr'];
					$amt_dr += $a_o['opdr'];
				}
			}



			if($amt != 0)
				$return_array[] = array('id'=>$r[$groupByField[2]],$groupByField[2]=>$r[$groupByField[2]],'Amount'=>$amt/*. ($amt_dr > $amt_cr ? ' Dr':' Cr')*/);
		}
		return $return_array;

	}

	function accounts(){
		return $this->add('Model_Account')->addCondition('scheme_id',$this->id);
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