<?php
class Model_Account_Loan extends Model_Account{
	
	public $transaction_deposit_type = TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in Loan Account {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Loan');
		$this->addCondition('SchemeType','Loan');

		$this->getElement('Amount')->caption('Loan Amount');
		$this->getElement('CurrentInterest')->caption('Panelty');

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod MONTH)";
		});

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array(),$form=null, $created_at = null ){

		throw $this->exception($form['LoanAgainstAccount_id'], 'ValidityCheck')->setField('AccountNumber');
		throw $this->exception('Check Loan Against Security', 'ValidityCheck')->setField('AccountNumber');

		if(!$created_at) $created_at = $this->api->now;

		if($form['LoanAgSecurity']){
			$security_account = $this->add('Model_Account')->load($form['LoanAgainstAccount_id']);
			if($security_account['LockingStatus']) throw $this->exception('Account is Already Locked','ValidityCheck')->setField('LoanAgainstAccount');
		}

		$new_account_id = parent::createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues,$form,$created_at);

		if($form['LoanAgSecurity']){
			$security_account['LockingStatus']=true;
			$security_account->save();
		}

		$scheme = $this->ref('scheme_id');
		$ProcessingFees = $scheme['ProcessingFees'];
		
		if($scheme['ProcessingFeesinPercent']){
			$ProcessingFees = $ProcessingFees * $this['Amount'] / 100;
		}

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_LOAN_ACCOUNT_OPEN,$this->ref('branch_id'),null, "Loan Account Openned ". $this['AccountNumber'], null, array('reference_account_id'=>$this->id));
		
		$transaction->addDebitAccount($this, $ProcessingFees);
		$transaction->addCreditAccount($this['Code'] . SP . PROCESSING_FEE_RECEIVED . $this['scheme_name'], $ProcessingFees);
		
		$transaction->execute();

		$documents=$this->add('Model_Document');
		foreach ($documents as $d) {
		 	if($form[$this->api->normalizeName($documents['name'])])
		 		$this->updateDocument($documents, $form[$this->api->normalizeName($documents['name'].' value')]);
		}

		$this->createPremiums();

	}


	function createPremiums(){
		if(!$this->loaded()) throw $this->exception('Account Must Be loaded to create premiums');
		
		$scheme = $this->ref('scheme_id');

		switch ($scheme['PremiumMode']) {
            case RECURRING_MODE_YEARLY:
                $toAdd = " +1 year";
                break;
            case RECURRING_MODE_HALFYEARLY:
                $toAdd = " +6 month";
                break;
            case RECURRING_MODE_QUATERLY:
                $toAdd = " +3 month";
                break;
            case RECURRING_MODE_MONTHLY:
                $toAdd = " +1 month";
                break;
            case RECURRING_MODE_DAILY:
                $toAdd = " +1 day";
                break;
        }

        $lastPremiumPaidDate = $this['created_at'];
        $rate = $scheme['Interest'];
        $premiums = $scheme['NumberOfPremiums'];
        if ($scheme['ReducingOrFlatRate'] == REDUCING_RATE) {
//          FOR REDUCING RATE OF INTEREST
            $emi = ($this('Amount') * ($rate / 1200) / (1 - (pow(1 / (1 + ($rate / 1200)), $premiums))));
        } else {
//          FOR FLAT RATE OF INTEREST
            $emi = (($this('Amount') * $rate * ($premiums + 1)) / 1200 + $this['Amount']) / $premiums;
        }
        $emi = round($emi);
        
        $prem = $this->add('Model_Premium');
        for ($i = 1; $i <= $premiums ; $i++) {
            $prem['account_id'] = $this->id;
            $prem['Amount'] = $emi;
            $lastPremiumPaidDate = $prem['DueDate'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($lastPremiumPaidDate)) . $toAdd));
            $prem->saveAndUnload();
        }

	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null,$on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		parent::deposit($amount,$narration,$accounts_to_debit,$form,$on_date);

		$this->payPremiums($amount,$on_date);
		$this->closeIfPaidCompletely();
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null){
		throw $this->exception('Withdrawl not supported in loan accounts', 'ValidityCheck')->setField('AccountNumber');
		// parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}

	function payPremiums($amount,$on_date){
		$PaidEMI = $this->ref('Premium')->addCondition('Paid','<>',0)->count()->getOne();
		$emi = $this->ref('Premium')->tryLoadAny()->get('Amount');
		$rate = $this->ref('scheme_id')->get('Interest');
		$premiums = $this->ref('scheme_id')->get('NumberOfPremiums');

		$interest = round((($emi * $premiums) - $this['Amount']) / $premiums); // Access amount then loan amount per premium is actually interest

		$PremiumAmountAdjusted = $PaidEMI * $emi;
		$AmountForPremiums = ($ac->CurrentBalanceCr + $amount) - $PremiumAmountAdjusted;

		$premiumsSubmited = (int) ($AmountForPremiums / $emi);

		if ($premiumsSubmited > 0) {
			$prem = $this->ref('Premium')->addCondition('Paid',false)->setOrder('id')->setLimit($premiumsSubmited);
		    foreach ($prem as $prem_array) {
		        $prem['PaidOn'] = $on_date;
		        $prem['Paid'] = true;
		        $prem->save();
		    }
		}

	}

	function closeIfPaidCompletely(){
		if (($this['CurrentBalanceDr'] - $this['CurrentBalanceCr']) <= 0) {
		    $this['ActiveStatus'] = false;
		    $this['affectsBalanceSheet'] = true;
		    $this->save();
		}
	}

	function postInterestEntry($on_date=null){
		// Applicable on all loan accounts that have their premium duedate on on_date

		if(!$on_date) $on_date = $this->api->now;
		if(!$this->loaded()) throw $this->exception('Account Must be loaded to post interest entry');

		$rate = $this['Interest'];
	    $premiums = $this['NumberOfPremiums'];

	    if ($this['ReducingOrFlatRate'] == REDUCING_RATE) {
	        // INTEREST FOR REDUCING RATE OF INTEREST
	        $emi = ($this['Amount'] * ($rate / 1200) / (1 - (pow(1 / (1 + ($rate / 1200)), $premiums))));
	        $interest = round((($emi * $premiums) - $this['Amount']) / $premiums);
	    }
	    if ($this['ReducingOrFlatRate'] == FLAT_RATE or $this['ReducingOrFlatRate'] == 0) {
			//    INTEREST FOR FLAT RATE OF INTEREST
	        $interest = round(($this['Amount'] * $rate * ($premiums + 1)) / 1200) / $premiums;
	    }

	    // $interest = interest value for one premium

	    $transaction = $this->add('Model_Transaction');
	    $transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_LOAN,$this->ref('branch_id'),$on_date, "Interest posting in Loan Account ".$this['AccountNumber'],null, array('reference_account_id'=>$this->id));
	    
	    $transaction->addDebitAccount($this, $interest);
	    $transaction->addCreditAccount($this->ref('branch_id')->get('Code') . SP . INTEREST_RECEIVED_ON . $this['scheme_name'], $interest);
	    
	    $transaction->execute();
	}

	function postPaneltyTransaction($on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT,$this->ref('branch_id'), $on_date, "Penalty deposited on Loan Account for ".date("F",strtotime($on_date)), null, array('reference_account_id'=>$this->id));

		$amount = $this['CurrentInerest'] < 300?:300;
		
		$transaction->addDebitAccount($this, $amount);
		$transaction->addCreditAccount($this->ref('branch_id')->get('Code') . SP . PENALTY_DUE_TO_LATE_PAYMENT_ON . $this['scheme_name'], $amount);
		
		$transaction->execute();

	}

}