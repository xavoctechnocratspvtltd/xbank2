<?php
class Model_Scheme_Loan extends Model_Scheme {

	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_LOAN;
	public $schemeGroup = ACCOUNT_TYPE_LOAN;

	function init(){
		parent::init();

		$this->getElement('type')->group('a~2~Basic Details')->mandatory(true);
		$this->getElement('name')->group('a~8~Basic Details');
		$this->getElement('ActiveStatus')->group('a~2~Basic Details');
		
		$this->getElement('Interest')->group('b~2~Product Details')->mandatory(true);
		$this->getElement('ReducingOrFlatRate')->group('b~2~Product Details')->mandatory(true);
		$this->getElement('PremiumMode')->group('b~2~Product Details')->mandatory(true);
		$this->getElement('NumberOfPremiums')->group('b~2~Product Details')->mandatory(true)->type('Number');
		$this->getElement('ProcessingFees')->group('b~2~Product Details')->mandatory(true)->type('Number');
		$this->getElement('ProcessingFeesinPercent')->group('b~2~Product Details');
		
		$this->getElement('balance_sheet_id')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('SchemeGroup')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('MinLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(0);
		$this->getElement('MaxLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(-1);
		
		$this->getElement('panelty')->group('d~6~Scheme Panelty')->type('int')->defaultValue(null)->hint('value of percent only number like 10, 12 etc');
		$this->getElement('panelty_grace')->group('d~6~Scheme Panelty')->defaultValue(0)->type('int')->hint('Number of grace days for panelty after premium submit');


		$this->getElement('ProcessingFeesinPercent')->caption('Check if Processing Fee in %');
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('type')->enum(explode(",",LOAN_TYPES));

		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		// $this->getElement('ReducingOrFlatRate')->destroy();
		$this->getElement('AccountOpenningCommission')->destroy();
		$this->getElement('Commission')->destroy();
		$this->getElement('PostingMode')->destroy();
		$this->getElement('CreateDefaultAccount')->destroy();
		$this->getElement('MaturityPeriod')->destroy();
		$this->getElement('InterestToAnotherAccountPercent')->destroy();
		$this->getElement('isDepriciable')->destroy();
		$this->getElement('DepriciationPercentBeforeSep')->destroy();
		$this->getElement('DepriciationPercentAfterSep')->destroy();
		$this->getElement('AgentSponsorCommission')->destroy();
		$this->getElement('CollectorCommissionRate')->destroy();
		$this->getElement('published')->destroy();
		$this->getElement('InterestToAnotherAccount')->destroy();
		// $this->getElement('AccountOpenningCommission')->destroy();
		
		$this->addCondition('SchemeType',$this->schemeType);

		$this->addHook('beforeSave',array($this,'beforeLoanSchemeSave'));
		// $this->addHook('schemeFormSubmitted',$this);
		
		//$this->add('dynamic_model/Controller_AutoCreator');
	}



	function beforeLoanSchemeSave(){
		if(!$this['type'])
			throw $this->exception('Please Specify Loan type', 'ValidityCheck')->setField('LoanType');
	}


	function getDefaultAccounts(){
		// change account name if scheme is created after 31-march-2019 
		if(strtotime($this->app->today) > strtotime('2019-03-31')){
			return array(
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Interest Received On",'Group'=>'Interest Received On {{Loan}}','PAndLGroup'=>'Interest Received On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Pre Interest Received On",'Group'=>'Pre Interest Received On {{Loan}}','PAndLGroup'=>'Pre Interest Received On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Penal Interest Received On",'Group'=>'Penal Interest Received On {{Loan}}','PAndLGroup'=>'Penal Interest Received On Loan'),
				array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Time Over Interest On",'Group'=>'Time Over Interest On {{Loan}}','PAndLGroup'=>'Time Over Interest On Loan'),
			);

		}else{
			return array(
					array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Interest Received On",'Group'=>'Interest Received On {{Loan}}','PAndLGroup'=>'Interest Received On Loan'),
					array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Processing Fee Received On",'Group'=>'Processing Fee Received On {{Loan}}','PAndLGroup'=>'Processing Fee Received On Loan'),
					array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Penalty Due To Late Payment On",'Group'=>'Penalty Due To Late Payment On {{Loan}}','PAndLGroup'=>'Penalty Due To Late Payment On Loan'),
					// array('under_scheme'=>"Indirect Income",'intermediate_text'=>"For Close Account On",'Group'=>'For Close Account On {{Loan}}','PAndLGroup'=>'For Close Account On Loan'),
					array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Time Over Charge On",'Group'=>'Time Over Charge On {{Loan}}','PAndLGroup'=>'Time Over Charge On Loan'),
					// array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Conveyence Charge Received On",'Group'=>'Conveyence Charge Received On {{Loan}}','PAndLGroup'=>'Conveyence Charge Received On Loan'),
					// array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Rent Charge Received On",'Group'=>'Rent Charge Received On {{Loan}}','PAndLGroup'=>'Rent Charge Received On Loan'),
					// array('under_scheme'=>"Indirect Income",'intermediate_text'=>"Legal Charge Received On",'Group'=>'Legal Charge Received On {{Loan}}','PAndLGroup'=>'Legal Charge Received On Loan'),
				);			
		}

	}

	function daily($branch= null,$on_date=null,$test_account=null){

		if(!$on_date) $on_date = $this->api->now;
		if(!$branch) $branch = $this->api->current_branch;

		$this->api->markProgress('Updating_Penalties',0);

		$this->putPaneltiesOnAllUnpaidLoanPremiums($branch,$on_date,$test_account);
		$this->api->markProgress('Updating_Penalties',null);
		
		$loan_accounts  = $this->add('Model_Active_Account_Loan');
		$q= $loan_accounts->dsql();
		
		$dealer_join = $loan_accounts->leftJoin('dealers','dealer_id');
		$dealer_join->addField('loan_panelty_per_day');

		$loan_accounts->scheme_join->addField('Interest');
		$loan_accounts->scheme_join->addField('NumberOfPremiums');
		$loan_accounts->scheme_join->addField('ReducingOrFlatRate');
		$loan_accounts->scheme_join->addField('panelty');
		$loan_accounts->scheme_join->addField('panelty_grace');

		$loan_accounts->leftJoin('premiums.account_id')
						->addField('DueDate');

		$loan_accounts->addExpression('due_panelty')->set(function($m,$q)use($on_date){
			return $m->refSQL('Premium')->addCondition('PaneltyCharged','<>',$m->api->db->dsql()->expr('PaneltyPosted'))->addCondition('DueDate','<=',$on_date)->sum($m->dsql()->expr('PaneltyCharged - PaneltyPosted'));
		});

		$loan_accounts->addCondition('branch_id',$branch->id);
		$loan_accounts->addExpression('calculated_due_date')->set(function($m,$q){
			return $q->expr('DATE_ADD([due_date],INTERVAL (IF([panelty]>0,IFNULL([panelty_grace],0),0)) DAY)',
				[
					'due_date'=>$m->getElement('DueDate'),
					'panelty_grace'=>$m->getElement('panelty_grace'),
					'panelty'=>$m->getElement('panelty')
				]);
		})->type('date');

		$loan_accounts->addCondition('calculated_due_date',$on_date);
		
        // $loan_accounts->addCondition(
        // 		$q->expr('IF([loan_panelty_per_day] is not null,DueDate,DATE_ADD(DueDate, INTERVAL IFNULL([panelty_grace],0)+1 DAY ))',['loan_panelty_per_day'=>$loan_accounts->getElement('loan_panelty_per_day'),'panelty_grace'=>$loan_accounts->getElement('panelty_grace')]),
        // 		$this->api->nextDate($on_date));
        // $loan_accounts->addCondition('DueDate','like',$this->api->nextDate($on_date).' %');
		if($test_account) $loan_accounts->addCondition('id',$test_account->id);
		
		// Copy to let be Deactivated in some process in between
		// So, its Not active account
		// will load it by active account id in loop
		$loan_accounts_copy  = $this->add('Model_Account_Loan');
		$q= $loan_accounts_copy->dsql();
		$dealer_join = $loan_accounts_copy->leftJoin('dealers','dealer_id');
		$dealer_join->addField('loan_panelty_per_day');
		$loan_accounts_copy->scheme_join->addField('Interest');
		$loan_accounts_copy->scheme_join->addField('NumberOfPremiums');
		$loan_accounts_copy->scheme_join->addField('ReducingOrFlatRate');
		$loan_accounts_copy->scheme_join->addField('panelty');
		$loan_accounts_copy->scheme_join->addField('panelty_grace');

		$loan_accounts_copy->leftJoin('premiums.account_id')
						->addField('DueDate');

		// Joining from premium will result in multiple records for sme account and hence a 
		// load on code ...
		$loan_accounts_copy->_dsql()->group('AccountNumber');


		$loan_accounts_copy->addExpression('due_panelty')->set(function($m,$q)use($on_date){
			return $m->refSQL('Premium')->addCondition('PaneltyCharged','<>',$m->api->db->dsql()->expr('PaneltyPosted'))->addCondition('DueDate','<=',$on_date)->sum($m->dsql()->expr('PaneltyCharged - PaneltyPosted'));
		});


		// $loan_accounts_copy->addCondition('branch_id',$branch->id);

        // $loan_accounts_copy->addCondition($q->expr('IF([loan_panelty_per_day] is not null,DueDate,DATE_ADD(DueDate, INTERVAL IFNULL([panelty_grace],0) DAY ))',['loan_panelty_per_day'=>$loan_accounts_copy->getElement('loan_panelty_per_day'),'panelty_grace'=>$loan_accounts_copy->getElement('panelty_grace')]),$this->api->nextDate($on_date));
		// $loan_accounts_copy->addCondition('DueDate','like',$this->api->nextDate($on_date).' %');
		// if($test_account) $loan_accounts_copy->addCondition('id',$test_account->id);

		$i=0;
		$total_count = $loan_accounts->count()->getOne();
		foreach ($loan_accounts as $acc_array) {
			$this->api->markProgress('Loan_Interest_and_Panelty',$i++,$acc_array['AccountNumber'],$total_count);
			$loan_accounts_copy->load($loan_accounts->id);
            $loan_accounts_copy->postInterestEntry($on_date);
			if($loan_accounts_copy['due_panelty'] > 0)
				$loan_accounts_copy->postPanelty($on_date);
		}
		$this->api->markProgress('Loan_Interest_and_Panelty',null);

		// all accounts that has passed their last EMI and has some panelty to be posted
		$time_over_accounts_with_panelty = $this->add('Model_Active_Account_Loan');
		$time_over_accounts_with_panelty->addExpression('due_panelty')->set(function($m,$q)use($on_date){
			return $m->refSQL('Premium')->addCondition('PaneltyCharged','<>',$m->api->db->dsql()->expr('PaneltyPosted'))->addCondition('DueDate','<',$on_date)->sum($m->dsql()->expr('PaneltyCharged - PaneltyPosted'));
		});

		$time_over_accounts_with_panelty->addExpression('last_emi')->set(function($m,$q)use($on_date){
			return $m->refSQL('Premium')->setOrder('DueDate','desc')->setLimit(1)->fieldQuery('DueDate');
		});

		$time_over_accounts_with_panelty->addCondition('branch_id',$branch->id);
		$time_over_accounts_with_panelty->addCondition('due_panelty','>',0);
        $time_over_accounts_with_panelty->addCondition('last_emi','<=',$this->api->previousMonth($on_date));

		if($test_account) $time_over_accounts_with_panelty->addCondition('id',$test_account->id);

		$total_count = $time_over_accounts_with_panelty->count()->getOne();
		$i=1;
		foreach ($time_over_accounts_with_panelty as $junk) {
			$time_over_accounts_with_panelty->postPanelty($on_date);
			$this->api->markProgress('Loan_Post_Panelty_For_Timeover',$i++,$junk['AccountNumber'],$total_count);
		}
		$this->api->markProgress('Loan_Post_Panelty_For_Timeover',null);

		// Shifted to post Penalty Function for each account
		// TODOS: Bring back here for performance reason.
		// $p_m=$this->add('Model_Premium');
  //       $p_m->join('accounts','account_id')->addField('branch_id');
  //       $p_m->_dsql()->set('PaneltyPosted',$p_m->dsql()->expr('PaneltyCharged'));
  //       $p_m->_dsql()->where('DueDate','<=',$on_date);
  //       $p_m->_dsql()->where('branch_id',$branch->id);
        
  //       if($test_account) $p_m->_dsql()->where('account_id',$test_account->id);
        
  //       $p_m->_dsql()->update();
	}

	// Not related with Any account ... general for all accounts
	function putPaneltiesOnAllUnpaidLoanPremiums($branch=null,$on_date=null,$test_account=null){
		if(!$on_date) $on_date = $this->api->now;
		if(!$branch) $branch = $this->api->current_branch;

		// if scheme has panelty then apply scheme panelty % else dealer panelty
		$premiums = $this->add('Model_Premium');
		$q = $premiums->dsql();
		$account_join = $premiums->leftJoin('accounts','account_id');
		$account_join->addField('branch_id');
		// Now penalty should be on Loans even against Deposit account IF EFFECTED BY SCHEME PANELTY PERCENTAGES
		$account_join->addField('LoanAgainstAccount_id');
		$dealer_join = $account_join->leftJoin('dealers','dealer_id');
		$dealer_join->addField('loan_panelty_per_day');
		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('panelty');
		$scheme_join->addField('panelty_grace');
		
		$premiums->addExpression('calculated_due_date')->set(function($m,$q){
			return $q->expr('DATE_ADD([duedate],INTERVAL (IF([panelty]>0,[panelty_grace],1)) DAY)',
				[
					'duedate'=>$m->getElement('DueDate'),
					'panelty_grace'=>$m->getElement('panelty_grace'),
					'panelty'=>$m->getElement('panelty')
				]);
		})->type('date');

		$premiums->addCondition('calculated_due_date',"<=",$on_date);

		$max_panelty_to_post = $this->api->db->dsql()->expr('IF( [panelty] > 0,(round([amount] * [panelty] /100)),([loan_panelty_per_day] * 30) )',
			[
				'amount'=>$premiums->getElement('Amount'),
				'panelty'=>$premiums->getElement('panelty'),
				'loan_panelty_per_day'=>$premiums->getElement('loan_panelty_per_day')
			]);

		$panelty_to_post = $this->api->db->dsql()->expr('IF( [panelty] > 0,(round([amount] * [panelty] /100)),( IF(([loan_panelty_per_day] + [panelty_charged]) > 300,300,([loan_panelty_per_day] + [panelty_charged])) ) )',
			[
				'amount'=>$premiums->getElement('Amount'),
				'panelty'=>$premiums->getElement('panelty'),
				'loan_panelty_per_day'=>$premiums->getElement('loan_panelty_per_day'),
				'panelty_charged'=>$premiums->getElement('PaneltyCharged')
			]);
		$premiums->addCondition('PaneltyCharged','<',$max_panelty_to_post);
		$premiums->addCondition('Paid',false);
		$premiums->addCondition('branch_id',$branch->id);
		// No Panelties for Loan Against Deposit Accounts
		$premiums->addCondition($premiums->dsql()->expr('([0] is null or [0] = 0)',array($premiums->getElement('LoanAgainstAccount_id'))));
		if($test_account) $premiums->addCondition('account_id',$test_account->id);
		
		$premiums->_dsql()->set('PaneltyCharged',$panelty_to_post);
        $premiums->_dsql()->sql_templates['update']="update [table] [join] set [set] [where]  [group] [having] [order] [limit]";
        $premiums->_dsql()->update();
		return $premiums;

//-------------------------------------------------------------------------------------------
		// Works all if dealer has some loan_panelty_per_day
		// $premiums = $this->add('Model_Premium');
		// $account_join = $premiums->leftJoin('accounts','account_id');
		// $account_join->addField('branch_id');
		// // Now penalty should be on Loans even against Deposit account IF EFFECTED BY SCHEME PANELTY PERCENTAGES
		// $account_join->addField('LoanAgainstAccount_id');
		// $dealer_join = $account_join->leftJoin('dealers','dealer_id');
		// $dealer_join->addField('loan_panelty_per_day');

		// $premiums->addCondition('loan_panelty_per_day','>=',0); // Assures only dealer with loan_panelty_per_day records are affected
		
		// $premiums->addCondition('DueDate','<=',$on_date);
		// $premiums->addCondition('PaneltyCharged','<',$this->api->db->dsql()->expr('loan_panelty_per_day * 30'));
		// $premiums->addCondition('Paid',false);
		// $premiums->addCondition('branch_id',$branch->id);

		// // No Panelties for Loan Against Deposit Accounts
		// $premiums->addCondition($premiums->dsql()->expr('([0] is null or [0] = 0)',array($premiums->getElement('LoanAgainstAccount_id'))));

		// if($test_account) $premiums->addCondition('account_id',$test_account->id);

		// $premiums->_dsql()->set('PaneltyCharged',$this->api->db->dsql()->expr('PaneltyCharged +'. $dealer_join->table_alias.'.loan_panelty_per_day'));
		// // if($test_account) $premiums->_dsql()->where('account_id',$test_account->id);
  //       $premiums->_dsql()->sql_templates['update']="update [table] [join] set [set] [where]  [group] [having] [order] [limit]";
        
  //       $premiums->_dsql()->update();


  //       // Works all below if scheme has some panelty defined
  //       $premiums = $this->add('Model_Premium');
        
  //       $q= $premiums->dsql();

		// $account_join = $premiums->leftJoin('accounts','account_id');
		// $account_join->addField('branch_id');
		// $account_join->addField('LoanAgainstAccount_id');
		// $dealer_join = $account_join->leftJoin('dealers','dealer_id');
		// $dealer_join->addField('loan_panelty_per_day');
		// $scheme_join = $account_join->join('schemes','scheme_id');
		// $scheme_join->addField('panelty');
		// $scheme_join->addField('panelty_grace');

		// $premiums->addCondition($q->expr('DATE_ADD(DueDate, INTERVAL IFNULL([0],0) DAY ) <= "[1]"',[$premiums->getElement('panelty_grace'),$on_date]));
		// $premiums->addCondition('loan_panelty_per_day',null); // Assures only dealer WITHOUT loan_panelty_per_day records are affected
		// $premiums->addCondition('panelty','>',0); // Assures only accounts with schemes WITH panelty records are affected
		
		// $panelty_to_post = $this->api->db->dsql()->expr('round([0] * [1] /100)',[$premiums->getElement('Amount'),$premiums->getElement('panelty')]);
		// $premiums->addCondition('PaneltyCharged','<',$panelty_to_post);
		// $premiums->addCondition('Paid',false);
		// $premiums->addCondition('branch_id',$branch->id);

		// // No Panelties for Loan Against Deposit Accounts -- Now removed 
		// // Now penalty should be on Loans even against Deposit account IF EFFECTED BY SCHEME PANELTY PERCENTAGES
		// // $premiums->addCondition($premiums->dsql()->expr('([0] is null or [0] = 0)',array($premiums->getElement('LoanAgainstAccount_id'))));

		// if($test_account) $premiums->addCondition('account_id',$test_account->id);

		// $premiums->_dsql()->set('PaneltyCharged',$panelty_to_post);
		// // if($test_account) $premiums->_dsql()->where('account_id',$test_account->id);
  //       $premiums->_dsql()->sql_templates['update']="update [table] [join] set [set] [where]  [group] [having] [order] [limit]";
  //       $premiums->_dsql()->update();
	}



	function monthly($branch=null, $on_date=null,$test_account=null){
		if(!$branch) $branch = $this->api->current_branch;
		if(!$on_date) $on_date = $this->api->now;


	}

	function halfYearly( $branch=null, $on_date=null, $test_account=null ) {
	}

	function yearly( $branch=null, $on_date=null, $test_account=null ) {
	}

}