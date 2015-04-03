<?php
class Model_Scheme_SavingAndCurrent extends Model_Scheme {

	public $loanType = true;
	public $schemeType = ACCOUNT_TYPE_BANK;
	public $schemeGroup = ACCOUNT_TYPE_BANK;

	function init() {
		parent::init();

		$this->getElement( 'type' )->enum( array( 'Saving', 'Current' ) );
		$this->getElement( 'ProcessingFeesinPercent' )->destroy();
		$this->getElement( 'InterestMode' )->destroy();
		$this->getElement( 'InterestRateMode' )->destroy();
		$this->getElement( 'Commission' )->destroy();
		$this->getElement( 'PostingMode' )->destroy();
		$this->getElement( 'PremiumMode' )->destroy();
		$this->getElement( 'ProcessingFees' )->destroy();
		$this->getElement( 'CreateDefaultAccount' )->destroy();
		$this->getElement( 'InterestToAnotherAccountPercent' )->destroy();
		$this->getElement( 'AgentSponsorCommission' )->destroy();
		$this->getElement( 'CollectorCommissionRate' )->destroy();
		$this->getElement( 'published' )->destroy();
		$this->getElement( 'ReducingOrFlatRate' )->destroy();
		$this->getElement( 'NumberOfPremiums' )->destroy();
		$this->getElement( 'InterestToAnotherAccount' )->destroy();
		$this->getElement( 'MaturityPeriod' )->destroy();
		$this->getElement( 'AccountOpenningCommission' )->destroy();
		$this->getElement( 'isDepriciable' )->destroy();
		$this->getElement( 'DepriciationPercentBeforeSep' )->destroy();
		$this->getElement( 'DepriciationPercentAfterSep' )->destroy();

		$this->getElement( 'balance_sheet_id' )->caption( 'Head' )->getModel()->addCondition( 'name', 'Deposits - Liabilities' );

		$this->addCondition( 'SchemeType', $this->schemeType );

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getDefaultAccounts() {
		return array(
			array( 'under_scheme'=>"Indirect Expenses", 'intermediate_text'=>COMMISSION_PAID_ON, 'Group'=>'Commission Paid On Saving and Current', 'PAndLGroup'=>'Commission Paid On Deposit' ),
			array( 'under_scheme'=>"Indirect Expenses", 'intermediate_text'=>INTEREST_PAID_ON, 'Group'=>'Interest Paid On Saving and Current', 'PAndLGroup'=>'Interest Paid On Deposit' ),
			array( 'under_scheme'=>"Indirect Income", 'intermediate_text'=>MINIMUM_BALANCE_CHARGE_RECEIVED_ON, 'Group'=>'Minimum Balance Charge Received On Saving and Current', 'PAndLGroup'=>'Minimum Balance Charge Received On Saving and Current' ),
			array( 'under_scheme'=>"Indirect Income", 'intermediate_text'=>CHEQUEBOOK_CHARGE_RECEIVED_ON, 'Group'=>'ChequeBook Charge Received On Saving and Current', 'PAndLGroup'=>'ChequeBook Charge Received On Saving and Current' ),
			array( 'under_scheme'=>"Indirect Income", 'intermediate_text'=>STATEMENT_CHARGE_RECEIVED_ON, 'Group'=>'Statement Charge Received On Saving and Current', 'PAndLGroup'=>'Statement Charge Received On Saving and Current' ),
		);
	}

	function daily( $branch=null, $on_date=null, $test_account=null ) {
		if ( !$branch ) $branch=$this->api->current_branch;

		$sbca_account = $this->add( 'Model_Account_SavingAndCurrent' );
		$sbca_account->addCondition( 'ActiveStatus', true );
		$sbca_account->addCondition( 'branch_id', $branch->id );
		$sbca_account->addCondition( 'created_at', '<', $on_date );

		$sbca_account->addExpression( 'min_limit' )->set( function( $m, $q ) {
				return $m->refSQL( 'scheme_id' )->fieldQuery( 'MinLimit' );
			} );

		$sbca_account->addExpression( 'balance' )->set( '((OpeningBalanceCr + CurrentBalanceCr) - (OpeningBalanceDr + CurrentBalanceDr) )' );

		$sbca_account->addCondition( 'balance', '<', $sbca_account->dsql()->expr( 'min_limit' ) );

		if ( $test_account ) $sbca_account->addCondition( 'id', $test_account->id );

		foreach ( $sbca_account as $accounts_array ) {
			if ( !$sbca_account->isMinBalanceChargeAppliedInThisQuarter( $on_date ) )
				$sbca_account->applyMinBalanceCharge( $on_date );
		}
	}

	function monthly( $branch=null, $on_date=null, $test_account=null ) {
	}

	function quarterly( $branch=null, $on_date=null, $test_account=null ) {

	}

	function halfYearly( $branch=null, $on_date=null, $test_account=null ) {
		if ( !$branch ) $branch=$this->api->current_branch;

		$sbca_account = $this->add( 'Model_Account_SavingAndCurrent' );
		$sbca_account->addCondition( 'ActiveStatus', true );
		$sbca_account->addCondition( 'branch_id', $branch->id );
		$sbca_account->addCondition( 'created_at', '<', $on_date );

		if ( $test_account ) $sbca_account->addCondition( 'id', $test_account->id );

		foreach ( $sbca_account as $accounts_array ) {
			$sbca_account->applyHalfYearlyInterest( $on_date );
		}
	}

	function yearly( $branch=null, $on_date=null, $test_account=null ) {
	}
}