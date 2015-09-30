<?php
class Model_Account_FixedAndMis extends Model_Account{
	
	public $transaction_deposit_type = TRA_FIXED_ACCOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in FD Account {{AccountNumber}}";	

	public $transaction_withdraw_type = TRA_FD_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_withdraw_narration = "Amount withdrawl from {{SchemeType}} Account {{AccountNumber}}";

	function init(){
		parent::init();

		$this->getElement('account_type')->enum(array('FD','MIS'))->mandatory(true);
		$this->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);

		$this->getElement('Amount')->caption('FD/MIS Amount');
		$this->getElement('AccountDisplayName')->caption('Account Name (IF Joint)');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod DAY)";
		});

		// $this->addHook('afterAccountDebited,afterAccountCredited',array($this,'closeIfPaidCompletely'));

		// $this->scheme_join->addField('Interest');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=array(),$form=null,$on_date=null,$in_branch=null){
		if(!$on_date) $on_date = $this->api->today;


		$balance = $this->getOpeningBalance($this->api->nextDate($on_date));
		$balance = round($balance['CR'] - $balance['DR'],0);
		$min_limit= 0; // No minimum limit for FD .. Let all money be withdrawn

		if(round($amount,0) > (round($balance,0))){
			throw $this->exception('Cannot withdraw amount less then '. $balance ,'ValidityCheck')->setField('amount');
		}
		
		// $this['CurrentInterest'] = $this['CurrentInterest'] + $this->getSavingInterest($on_date);
		// $this['LastCurrentInterestUpdatedAt'] = $on_date;

		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date,$in_branch);
		// $this->save();
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber=null,$otherValues=null,$form=null,$created_at=null){
		if(!$AccountNumber) $AccountNumber = $this->getNewAccountNumber($otherValues['account_type']);

		$scheme = $this->add('Model_Scheme')->load($scheme_id);
		
		if($scheme['MinLimit']){
			if($otherValues['Amount']< $scheme['MinLimit'])
				throw $this->exception('Minimum Limit set as '.$scheme['MinLimit'], 'ValidityCheck')->setField('Amount');
		}

		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);
		$this->createInitialTransaction($created_at, $form);
		if($form['agent_id']){
			$this->giveAgentCommission($created_at);
			$this->agent()->addCRPB($this->scheme()->get('CRPB'),$this['Amount']);
		}
	}

	function createInitialTransaction($on_date, $form){

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_FIXED_ACCOUNT_DEPOSIT, $this->ref('branch_id'), $on_date, "Initial Fixed Amount Deposit in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		if($form['debit_account']){
			$debit_account = $form['debit_account'];
		}else{
			$debit_account = $this->ref('branch_id')->get('Code').SP.CASH_ACCOUNT;
		}

		$transaction->addDebitAccount($debit_account, $this['Amount']);
		$transaction->addCreditAccount($this, $this['Amount']);
		
		$transaction->execute();
	}

	function giveAgentCommission($on_date=null){
		// TODO :: To give commission .... 
		if(!$on_date) $on_date= $this->api->today;

		$commissionAmount = $this->api->getComission($this->ref('scheme_id')->get('AccountOpenningCommission'), OPENNING_COMMISSION);
        $commissionAmount = $commissionAmount * $this["Amount"] / 100.00;


        $commissionForThisAgent = $this->agent()->cadre()->selfEfectivePercentage() * $commissionAmount / 100.00;

        $transaction = $this->add('Model_Transaction');
        $transaction->createNewTransaction(TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $this->ref('branch_id'), $on_date, "Agent Account openning commision for ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
        
        $transaction->addDebitAccount($this['branch_code'] . SP . COMMISSION_PAID_ON . SP. $this['scheme_name'], $commissionForThisAgent);

        $agent_saving_account = $this->ref('agent_id')->ref('account_id');
        $tds_account = $this->add('Model_Account')->loadBy('AccountNumber',$this['branch_code'].SP.BRANCH_TDS_ACCOUNT);

        $tds_amount = round((strlen($agent_saving_account->ref('member_id')->get('PanNo'))==10)? $commissionForThisAgent * 10 /100 : $commissionForThisAgent * 20 /100,2);
		
		$saving_amount = $commissionForThisAgent - $tds_amount;        

        $transaction->addCreditAccount($agent_saving_account, $saving_amount);
        $transaction->addCreditAccount($tds_account, $tds_amount);
        
        $transaction->execute();

        $this->propogateAgentCommission($debit_acocunt = $this['branch_code'] . SP . COMMISSION_PAID_ON . SP. $this['scheme_name'], $total_commission_amount = $commissionAmount, $on_date);


	}

	// function getFDMISInterest($on_date){
	// 	// (a.CurrentInterest + (a.CurrentBalanceCr * $sc->Interest * DATEDIFF('" . $i . "', a.LastCurrentInterestUpdatedAt)/36500)
	// 	$days = $this->api->my_date_diff($on_date, $this['LastCurrentInterestUpdatedAt']);
	// 	return $this['CurrentInterest'] + ($this['CurrentBalanceCr'] * $this['Interest'] * $days['days_total'] / 36500);
	// }

	function getAmountForInterest($on_date=null,$calculate_remainig_days=true){
		
		if(!$on_date) $on_date = $this->api->today;

		$on_amount = $this['Amount'];

		$days = $this->api->my_date_diff($on_date,date("Y-m-d",strtotime($this['created_at'])));

		$years_completed = (int) (($days['days_total']) / 365) ;
		$remaining_days = ($days['days_total'] % 365) ;
		
		$interest_rate = $this['Interest'];
		if(!$interest_rate) $interest_rate = $this->ref('scheme_id')->get('Interest');

		for ($i=0; $i < $years_completed; $i++) { 
			$interest = $on_amount * $interest_rate / 100;
			$on_amount += $interest;
		}

		if($remaining_days and $calculate_remainig_days){
			$interest = $on_amount * $interest_rate / 36500 * ($remaining_days);
			$on_amount += $interest;
		}

		// echo "doing provision on $on_amount on $on_date for ".$days['days_total']." days and years completed = $years_completed <br/>";
		return $on_amount;
	}

	function doInterestProvision($on_date,$maturity_day=false){
		// a.CurrentInterest=(a.CurrentBalanceCr * s.Interest * DATEDIFF('" . getNow("Y-m-d") . "', a.LastCurrentInterestUpdatedAt)/36500), a.LastCurrentInterestUpdatedAt='" . getNow("Y-m-d") . "' WHERE
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to post Interest in it');

		$days = $this->api->my_date_diff($on_date,$this['LastCurrentInterestUpdatedAt']);

		// Count One more day when FD is openned
		if($this['LastCurrentInterestUpdatedAt'] == $this['created_at']) $days['days_total']++;
		// Deduct One Day from last day of maturity
		if($maturity_day) $days['days_total']--;

		$interest = $this->getAmountForInterest($on_date,false) * $this['Interest'] * $days['days_total'] / 36500;
	
	
		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		$this['CurrentInterest'] = $this['CurrentInterest'] + $interest;

	    $debitAccount = $this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'];
		$creditAccount = $this['branch_code'] . SP . INTEREST_PROVISION_ON . SP. $this['scheme_name'];

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_PROVISION_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date, "FD Interest Provision in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($debitAccount, $interest);
		$transaction->addCreditAccount($creditAccount, $interest);
		
		$transaction->execute();

		$this->api->markProgress('Doing_Provision',$this['AccountNumber'],$on_date,5);

		$this->save();
	}

	function markMature($on_date=null){
		if(!$on_date) $on_date = $this->api->today;
		
		if($maturity_to_account = $this->ifMaturitytoAnotherAccount()){
			$this->withdrawl($this['CurrentBalanceCr'],$narration='Maturity Amount Transfered to '. $maturity_to_account['AccountNumber'],$accounts_to_credit=array(array($maturity_to_account['AccountNumber']=>$this['CurrentBalanceCr'])),$form=null,$on_date,$in_branch=null,$reference_id=$this->id);
		}

		if($this->isAutoRenewed()){
			throw $this->exception('Auto Renew Process');
			$this->autoRenewFD();
		}
		$id=$this->id;
		$this['MaturedStatus'] = true;
		$this->saveAndUnload();
		return $this->add('Model_Account_FixedAndMis')->load($id);
	}

	function isAutoRenewed(){
		return false;
	}

	function autoRenewFD(){

	}

	function revertProvision($on_date){

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT, $this->ref('branch_id'), $on_date	, "Yearly/Maturity Interest posting to ". $this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		$debitAccount = $this['branch_code'] . SP . INTEREST_PROVISION_ON . SP. $this['scheme_name'];
		
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

		// 

		$creditAccount = $this->ref('intrest_to_account_id');

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, $this->ref('branch_id'), $on_date, "MIS monthly Interest Posting in ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($this['branch_code'].SP.'Interest Paid On'.SP.$this->ref('scheme_id')->get('name'), $interest);
		$transaction->addCreditAccount($this, $interest);
		
		$transaction->execute();

		// 

		try{

			$creditAccount = $this->ref('intrest_to_account_id');

			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_MIS_ACCOUNT, $this->ref('branch_id'), $on_date, "MIS monthly Interest Deposited from ".$this['AccountNumber']." to " . $creditAccount['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
			
			$transaction->addDebitAccount($this, $interest);
			$transaction->addCreditAccount($creditAccount, $interest);
			
			$transaction->execute();
		}catch(\Exception $e){
			echo $this['AccountNumber'];
			throw $e;
		}
		
		if($mark_matured) $this['MaturedStatus'] = true;
		$this->saveAndUnload();
		// throw $this->exception('interstToAnotherAccountEntry post entry to be checked');
	}

	function provisions($from_date, $to_date, $branch=null, $for_scheme=null, $for_account=null){
		if(!$branch) $branch = $this->api->currentBranch;
		
		$provision_transactions_rows = $this->add('Model_TransactionRow');
		$provision_transactions_rows->getElement('reference_id')->sortable(true);
		$provision_transactions_rows->getElement('amountDr')->sortable(true);

		$provision_transactions_join = $provision_transactions_rows->join('transactions','transaction_id');
		// $provision_transactions_join->addField('reference_id');
		$account_join = $provision_transactions_join->join('accounts','reference_id');
		$account_join->addField('AccountNumber');
		$account_join->addField('account_created','created_at')->sortable(true);
		$account_join->addField('account_braqnch_id','branch_id');

		$scheme_join = $account_join->join('schemes','scheme_id');
		$scheme_join->addField('scheme_name','name')->sortable(true);
		$scheme_join->addField('Interest');

		$transaction_type_join = $provision_transactions_join->join('transaction_types','transaction_type_id');

		$transaction_type_model = $this->add('Model_TransactionType');
		$transaction_type_model->tryLoadBy('name',TRA_INTEREST_PROVISION_IN_FIXED_ACCOUNT);

		if(!$transaction_type_model->loaded()) $transaction_type_model->save();

		$provision_transactions_rows->addCondition('transaction_type_id',$transaction_type_model->id);
		$provision_transactions_rows->addCondition('created_at','>=',$from_date);
		$provision_transactions_rows->addCondition('created_at','<',$this->api->nextDate($to_date));

		$provision_transactions_rows->addCondition('amountDr','>',0);
		// $provision_transactions_rows->_dsql()->group('transaction_id');

		return $provision_transactions_rows;

	}
}