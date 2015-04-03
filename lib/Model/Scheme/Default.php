<?php
class Model_Scheme_Default extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_DEFAULT;
	public $schemeGroup = ACCOUNT_TYPE_DEFAULT;

	function init(){
		parent::init();

		$this->getElement('ProcessingFeesinPercent')->destroy();
		$this->getElement('InterestMode')->destroy();
		$this->getElement('InterestRateMode')->destroy();
		$this->getElement('type')->defaultValue('Default');
		$this->getElement('Commission')->destroy();
		$this->getElement('PostingMode')->destroy();
		$this->getElement('PremiumMode')->destroy();
		$this->getElement('ProcessingFees')->destroy();
		$this->getElement('CreateDefaultAccount')->destroy();
		$this->getElement('InterestToAnotherAccountPercent')->destroy();
		$this->getElement('AgentSponsorCommission')->destroy();
		$this->getElement('CollectorCommissionRate')->destroy();
		$this->getElement('published')->destroy();
		$this->getElement('ReducingOrFlatRate')->destroy();
		$this->getElement('NumberOfPremiums')->destroy();
		$this->getElement('InterestToAnotherAccount')->destroy();
		$this->getElement('MaturityPeriod')->destroy();
		
		$this->getElement('balance_sheet_id')->caption('Head');
		$this->getElement('AccountOpenningCommission')->caption('Account Commissions(in %)');


		$this->addCondition('SchemeType',$this->schemeType);

		$this->addHook('beforeInsert',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}


	function beforeInsert($model){
		if($model['isDepriciable']){
			if(!$model['DepriciationPercentBeforeSep'])
				throw $this->exception('Please Specify value','ValidityCheck')->setField('DepriciationPercentBeforeSep');
			if(!$model['DepriciationPercentAfterSep'])
				throw $this->exception('Please Specify value','ValidityCheck')->setField('DepriciationPercentAfterSep');
		}		
	}
	function getDefaultAccounts(){
		return array(
				// These default accounts are required when a new branch created not when a new scheme of this type is created
				// array('under_scheme'=>'indirect expenses','intermediate_text'=>"",'Group'=>'ROUND OF','PAndLGroup'=>'ROUND OF'),
				// array('under_scheme'=>BRANCH_TDS_ACCOUNT,'intermediate_text'=>"",'Group'=>BRANCH_TDS_ACCOUNT,'PAndLGroup'=>BRANCH_TDS_ACCOUNT),
			);
	}

	function getBranchDefaultAccounts(){
		return array(
				// These default accounts are required when a new branch created not when a new scheme of this type is created
				array('under_scheme'=>'indirect expenses','intermediate_text'=>"",'Group'=>'ROUND OF','PAndLGroup'=>'ROUND OF'),
				array('under_scheme'=>BRANCH_TDS_ACCOUNT,'intermediate_text'=>"",'Group'=>BRANCH_TDS_ACCOUNT,'PAndLGroup'=>BRANCH_TDS_ACCOUNT),
			);
	}

	function daily($branch=null,$on_date=null,$test_account=null){
	}

	function monthly($branch=null, $on_date=null,$test_account=null){

	}

	function halfYearly( $branch=null, $on_date=null, $test_account=null ) {
	}

	function yearly( $branch=null, $on_date=null, $test_account=null ) {
		
		if(!$this['isDepriciable']) return;

		// Put Deprecations 
		$accounts = $this->ref('Account');
		$accounts->addCondition('ActiveStatus',true);
		$accounts->addCondition('created_at','<',$on_date);
		$accounts->addCondition('branch_id',$branch->id);

		if($test_account) $accounts->addCondition('id',$test_account->id);

		foreach ($accounts as $accounts_array) {
			if (strtotime($accounts['created_at']) > strtotime(date('Y',strtotime($on_date)) - 1 . "-09-30")) {
                $depr = $deperecation_schemes['DepriciationPercentAfterSep'];
            } else {
                $depr = $deperecation_schemes['DepriciationPercentBeforeSep'];
            }

            $depAmt = ($accounts['CurrentBalanceDr'] - $accounts['CurrentBalanceCr']) * $depr / 100;
//                    echo $depAmt;

            $transaction = $this->add('Model_Transaction');
            $transaction->createNewTransaction(TRA_DEPRICIATION_AMOUNT_CALCULATED, $branch, $on_date, "Depreciation amount calculated", $only_transaction=null, array('reference_account_id'=>$accounts->id));
            
            $transaction->addDebitAccount($b->Code . SP . DEPRECIATION_ON_FIXED_ASSETS, round($depAmt,COMMISSION_ROUND_TO));
            $transaction->addCreditAccount($accounts, round($depAmt,COMMISSION_ROUND_TO));
            
            $transaction->execute();
		}
	}
}