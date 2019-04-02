<?php
class Model_Scheme_Default extends Model_Scheme {
	
	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_DEFAULT;
	public $schemeGroup = ACCOUNT_TYPE_DEFAULT;

	function init(){
		parent::init();

		$this->getElement('type')->group('a~2~Basic Details')->mandatory(true);
		$this->getElement('name')->group('a~8~Basic Details');
		$this->getElement('ActiveStatus')->group('a~2~Basic Details');
		
		$this->getElement('Interest')->group('b~4~Product Details')->mandatory(true);
		$this->getElement('InterestToAnotherAccount')->group('b~4~Product Details')->mandatory(true);
		$this->getElement('MaturityPeriod')->group('b~4~Product Details')->mandatory(true)->type('Number');
		// $this->getElement('ProcessingFees')->group('b~2~Product Details')->mandatory(true)->type('Number');
		// $this->getElement('ProcessingFeesinPercent')->group('b~2~Product Details');
		
		$this->getElement('AccountOpenningCommission')->group('d~6~Commission')->mandatory(true);
		$this->getElement('CRPB')->group('d~6~Commission')->mandatory(true);
		

		$this->getElement('balance_sheet_id')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('SchemeGroup')->group('c~3~Accounts Details')->mandatory(true);
		$this->getElement('MinLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(0);
		$this->getElement('MaxLimit')->group('c~3~Accounts Details')->mandatory(true)->defaultValue(-1);



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
				// array('under_scheme'=>ADMISSION_FEE_ACCOUNT,'intermediate_text'=>"",'Group'=>'Admission Fees Received','PAndLGroup'=>'Admission Fees Received'),
				// array('under_scheme'=>'Adminssion Fee','intermediate_text'=>"",'Group'=>'Admission Fees Received','PAndLGroup'=>'Admission Fees Received'),
				// array('under_scheme'=>CASH_ACCOUNT_SCHEME,'intermediate_text'=>"",'Group'=>CASH_ACCOUNT,'PAndLGroup'=>CASH_ACCOUNT),
				
			);
	}

	function getBranchDefaultAccounts(){
		return array(
				// These default accounts are required when a new branch created not when a new scheme of this type is created
				array('under_scheme'=>'indirect expenses','intermediate_text'=>"",'Group'=>'ROUND OF','PAndLGroup'=>'ROUND OF'),
				array('under_scheme'=>BRANCH_TDS_ACCOUNT,'intermediate_text'=>"",'Group'=>BRANCH_TDS_ACCOUNT,'PAndLGroup'=>BRANCH_TDS_ACCOUNT),
				array('under_scheme'=>'indirect expenses','intermediate_text'=>"",'Group'=>'Conveyance Expenses','PAndLGroup'=>"Conveyance Expenses"),

				// BRANCH SPECIFIC GST ACCOUNT
				// array('under_scheme'=>CURRENT_LIABILITIES_SCHEME,'intermediate_text'=>DUTIES_TAXES_SCHEME." GST",'Group'=>DUTIES_TAXES_SCHEME." GST",'PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"CGST 9%",'Group'=>'GST 18%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"SGST 9%",'Group'=>'GST 18%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"IGST 18%",'Group'=>'IGST 18%','PAndLGroup'=>""),

				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"CGST 14%",'Group'=>'GST 28%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"SGST 14%",'Group'=>'GST 28%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"IGST 28%",'Group'=>'IGST 28%','PAndLGroup'=>""),

				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"CGST 2.5%",'Group'=>'GST 5%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"SGST 2.5%",'Group'=>'GST 5%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"IGST 5%",'Group'=>'IGST 5%','PAndLGroup'=>""),

				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"CGST 3%",'Group'=>'GST 6%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"SGST 3%",'Group'=>'GST 6%','PAndLGroup'=>""),
				// array('under_scheme'=>ACCOUNT_TYPE_GST,'intermediate_text'=>"IGST 6%",'Group'=>'IGST 6%','PAndLGroup'=>"")
			);
	}

	function daily($branch=null,$on_date=null,$test_account=null){
	}

	function monthly($branch=null, $on_date=null,$test_account=null){

	}

	function halfYearly( $branch=null, $on_date=null, $test_account=null ) {
	}

	function yearly( $branch=null, $on_date=null, $test_account=null ) {

		// Put Deprecations 
		$accounts = $this->add('Model_Account_Default');
		$accounts->addCondition('ActiveStatus',true);
		$accounts->addCondition('created_at','<',$on_date);
		$accounts->addCondition('branch_id',$branch->id);
		$accounts->addExpression('isDepriciable')->set($accounts->refSQL('scheme_id')->fieldQuery('isDepriciable'));
		$accounts->addExpression('DepriciationPercentAfterSep')->set($accounts->refSQL('scheme_id')->fieldQuery('DepriciationPercentAfterSep'));
		$accounts->addExpression('DepriciationPercentBeforeSep')->set($accounts->refSQL('scheme_id')->fieldQuery('DepriciationPercentBeforeSep'));
		$accounts->addCondition('isDepriciable',true);

		if($test_account) $accounts->addCondition('id',$test_account->id);

		foreach ($accounts as $account) {
			
			// before code ....
			// if (strtotime($account['created_at']) > strtotime(date('Y',strtotime($on_date)) - 1 . "-09-30")) {
   //              $depr = $account['DepriciationPercentAfterSep'];
   //          } else {
   //          	$depr = $account['DepriciationPercentBeforeSep'];
   //          }

   //          $depAmt = ($account['CurrentBalanceDr'] - $account['CurrentBalanceCr']) * $depr / 100;
//                    echo $depAmt;
			// end of before code ....

            // get opening balance on_date After-Sep
            // get opening balance on_date 30-Sep
            $op_bal_array_before_sep = $account->getOpeningBalance((date('Y',strtotime($on_date)) - 1).'-09-30');
            $op_bal_before_sep = $op_bal_array_before_sep['dr'] - $op_bal_array_before_sep['cr'];
            $depr = $account['DepriciationPercentBeforeSep'];
            $depAmt = ($op_bal_before_sep) * $depr / 100;
			 
			if($op_bal_before_sep > 0){
	            $transaction = $this->add('Model_Transaction');
	            $transaction->createNewTransaction(TRA_DEPRICIATION_AMOUNT_CALCULATED, $branch, $on_date, "Depreciation amount calculated full rate ( ".$depr."% )", $only_transaction=null, array('reference_id'=>$accounts->id));
	            
	            $transaction->addDebitAccount($account['branch_code'] . SP . DEPRECIATION_ON_FIXED_ASSETS, round($depAmt,COMMISSION_ROUND_TO));
	            $transaction->addCreditAccount($account, round($depAmt,COMMISSION_ROUND_TO));
	            $transaction->execute();
			}
			
            $op_bal_array_after_sep = $account->getOpeningBalance(date('Y',strtotime($on_date)).'-03-31');
            $op_bal_after_sep = ($op_bal_array_after_sep['dr'] - $op_bal_array_after_sep['cr']) - $op_bal_before_sep;
            $depr = $account['DepriciationPercentAfterSep'];
            $depAmt = ($op_bal_after_sep) * $depr / 100;
            if($op_bal_after_sep > 0 ){
            	$transaction = $this->add('Model_Transaction');
	            $transaction->createNewTransaction(TRA_DEPRICIATION_AMOUNT_CALCULATED, $branch, $on_date, "Depreciation amount calculated half rate ( ".$depr."% )", $only_transaction=null, array('reference_id'=>$accounts->id));
	            
	            $transaction->addDebitAccount($account['branch_code'] . SP . DEPRECIATION_ON_FIXED_ASSETS, round($depAmt,COMMISSION_ROUND_TO));
	            $transaction->addCreditAccount($account, round($depAmt,COMMISSION_ROUND_TO));
	            $transaction->execute();
            }

		}

		
		// Revert TDS entry for commissions less then 15,000/- or in config
		// Done but commented for year 18-19... should work in 2019-2020 though

		$this->add('Model_AgentTDS')->revertTdsEntries($on_date); // Working and local - tested function

	}
}