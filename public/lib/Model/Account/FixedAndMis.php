<?php
class Model_Account_FixedAndMis extends Model_Account{
	
	public $transaction_deposit_type = TRA_FIXED_ACCOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in FD Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->getElement('account_type')->enum(array('FD','MIS'));
		$this->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);

		$this->getElement('Amount')->caption('FD/MIS Amount');
		$this->getElement('AccountDisplayName')->caption('Account Name (IF Joint)');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod DAY)";
		});

		// $this->scheme_join->addField('Interest');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=null,$form=null,$created_at=null){

		$scheme = $this->add('Model_Scheme')->load($scheme_id);
		if($scheme['MinLimit']){
			if($otherValues['Amount']< $scheme['MinLimit'])
				throw $this->exception('Minimum Limit set as '.$scheme['MinLimit'], 'ValidityCheck')->setField('Amount');
		}

		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);
		$this->createInitialTransaction($created_at, $form);
		$this->giveAgentCommission();
	}

	function createInitialTransaction($on_date, $form){

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_FIXED_ACCOUNT_DEPOSIT, $this->ref('branch_id'), $on_date, "Initial Fixed Amount Deposit in ".$this['AccountNumber'], $only_transaction=null, array('reference_account_id'=>$this->id));
		
		if($form['debit_account']){
			$debit_account = $form['debit_account'];
		}else{
			$debit_account = $this->ref('branch_id')->get('Code').SP.CASH_ACCOUNT;
		}

		$transaction->addDebitAccount($debit_account, $this['Amount']);
		$transaction->addCreditAccount($this, $this['Amount']);
		
		$transaction->execute();
	}

	function giveAgentCommission(){
		// TODO :: To give commission .... 
	}

	// function getFDMISInterest($on_date){
	// 	// (a.CurrentInterest + (a.CurrentBalanceCr * $sc->Interest * DATEDIFF('" . $i . "', a.LastCurrentInterestUpdatedAt)/36500)
	// 	$days = $this->api->my_date_diff($on_date, $this['LastCurrentInterestUpdatedAt']);
	// 	return $this['CurrentInterest'] + ($this['CurrentBalanceCr'] * $this['Interest'] * $days['days_total'] / 36500);
	// }

	function doInterestProvision($on_date,$mark_matured=false){
		// a.CurrentInterest=(a.CurrentBalanceCr * s.Interest * DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to post Interest in it');

		$days = $this->api->my_date_diff($on_date,$this['LastCurrentInterestUpdatedAt']);
		$interest = $this['CurrentBalanceCr'] * $this['Interest'] * $days['days_total'] / 36500;
		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		$this['CurrentInterest'] = $this['CurrentInterest'] + $interest;


	    $debitAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . $this['scheme_name'];
		$creditAccount = $this['branch_code'] . SP . INTEREST_PROVISION_ON . $this['scheme_name'];

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_PROVISION_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date, "FD monthly Interest Deposited in ".$this['AccountNumber'], $only_transaction=null, array('reference_account_id'=>$this->id));
		
		$transaction->addDebitAccount($debitAccount, $interest);
		$transaction->addCreditAccount($creditAccount, $interest);
		
		$transaction->execute();

		if($mark_matured){
			$this['MaturedStatus'] = true;
			if($maturity_to_account = $this->ifMaturitytoAnotherAccount()){
				throw $this->exception('Maturity to account entry ??? ')->adddMoreInfo('Account',$this['AccountNumber']);
			}

			if($this->isAutoRenewed()){
				throw $this->exception('Auto Renew Process');
			}
		}

		return $this->saveAs('Model_Account_FixedAndMis');
	}

	function isAutoRenewed(){
		return false;
	}

	function revertProvision($on_date){

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date	, "Yearly/Maturity Interest posting to ". $this['AccountNumber'], $only_transaction=null, array('reference_account_id'=>$this->id));
		
		$debitAccount = $this['branch_code'] . SP . INTEREST_PROVISION_ON . $this['scheme_name'];
		
		$transaction->addDebitAccount($debitAccount, $this['CurrentInterest']);
		$transaction->addCreditAccount($this, $this['CurrentInterest']);
		
		$transaction->execute();

		$this['CurrentInterest'] = 0;
		$this->save();

	}

	function ifMaturitytoAnotherAccount(){
		$account=$this->ref('MaturityToAccount_id');
		if($account->loaded())
			return $account;
		else
			return false;
	}

	function interstToAnotherAccountEntry($on_date,$mark_matured=false){
		$days = $this->api->my_date_diff($on_date,$this['LastCurrentInterestUpdatedAt']);
		
		$interest = ( $this['CurrentBalanceCr'] - $this['CurrentBalanceDr'] ) * $this['Interest'] * $days['days_total'] / 36500;
		
		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		$this['CurrentInterest'] = $this['CurrentInterest'] + $interest;

		if($mark_matured) $this['MaturedStatus'] = true;

		$this->save();

		$creditAccount = $this->ref('intrest_to_account_id');

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date, "FD monthly Interest Deposited in ".$this['AccountNumber'], $only_transaction=null, array('reference_account_id'=>$this->id));
		
		$transaction->addDebitAccount($this, $interest);
		$transaction->addCreditAccount($creditAccount, $interest);
		
		$transaction->execute();
		// throw $this->exception('interstToAnotherAccountEntry post entry to be checked');
	}

	function provisions($from_date, $to_date, $for_scheme=null, $for_account=null){
		$provision_transactions_rows = $this->add('Model_TransactionRow');
		
		$provision_transactions_join = $provision_transactions_rows->join('transactions','transaction_type_id');
		$transaction_type_join = $provision_transactions_join->join('transaction_types','transaction_type_id');
		

		$transaction_type_model = $this->add('Model_TransactionType');
		$transaction_type_model->tryLoadBy('name',TRA_INTEREST_PROVISION_IN_FIXED_ACCOUNT);

		if(!$transaction_type_model->loaded()) $transaction_type_model->save();

		$provision_transactions_rows->addCondition('');

	}
}