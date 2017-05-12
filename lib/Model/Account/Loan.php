<?php
class Model_Account_Loan extends Model_Account{
	
	public $transaction_deposit_type = TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "Amount submited in Loan Account {{AccountNumber}}";	

	function init(){
		parent::init();


		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','Loan');
		$this->addCondition('SchemeType','Loan');

		$this->getElement('agent_id')->destroy();
		$this->getElement('collector_id')->destroy();
		$this->getElement('Amount')->caption('Loan Amount');
		$this->getElement('CurrentInterest')->caption('Panelty');
		$this->getElement('account_type')->enum(explode(",",LOAN_TYPES))->mandatory(true);
		$this->getElement('doc_image_id')->mandatory(false);

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->add('Model_Scheme')->addCondition('id',$q->getField('scheme_id'))->fieldQuery("NumberOfPremiums")->render().") MONTH)";
		});

		$this->addExpression('dealer_monthly_date')->set(function ($m,$q){
			return $m->refSQL('dealer_id')->fieldQuery('dealer_monthly_date');
		});

		$this->addHook('beforeSave',$this);
		$this->addHook('editing',$this);
		// $this->addHook('afterAccountDebited,afterAccountCredited',array($this,'closeIfPaidCompletely'));

		$this->add('Controller_Validator');
		$this->is(array(
							'AccountNumber|to_trim|unique'
						)
				);

		$this->is('account_type','if','Loan Against Deposit','[LoanAgainstAccount_id]!');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function editing(){
		$this->getELement('LoanInsurranceDate')->system(true);
		// $this->getELement('LoanAgainstAccount_id')->system(true);
	}

	function beforeSave(){
		if(!$this['account_type'])
			throw $this->exception('Please Specify Account Type', 'ValidityCheck')->setField('account_type');

		if($this['account_type'] == 'Loan Against Deposit' AND !$this['LoanAgainstAccount_id']){
			throw $this->exception('For Secured Loans Another Account is must','ValidityCheck')->setField('LoanAgainstAccount_id');
		}

		if($this->loaded() and $this['account_type'] == 'Loan Against Deposit' and $this->dirty['LoanAgainstAccount_id']){
			$old_locked_account = $this->newInstance()->load($this->id);
			$old_locked_account->ref('LoanAgainstAccount_id')->unlock();
			$this->ref('LoanAgainstAccount_id')->lock();
		}

	}

	function createNewPendingAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=null,$form=null,$created_at=null){
		if(!($branch instanceof Model_Branch) or !$branch->loaded()) throw $this->exception('Branch Must be Loaded Object of Model_Branch');
		if(!$created_at) $created_at = $this->api->now;
		if(!$otherValues) $otherValues=array();

		if($otherValues['account_type']==LOAN_AGAINST_DEPOSIT){
			if(!$otherValues['LoanAgainstAccount_id'])
				throw $this->exception('Please Specify Loan Against Account Number', 'ValidityCheck')->setField('LoanAgainstAccount');

		}else{
			if(!$otherValues['dealer_id'])
				throw $this->exception('Dealer is Must', 'ValidityCheck')->setField('dealer');
		}

		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->allow_any_name = true;

		$pending_account['member_id'] = $member_id;
		$pending_account['scheme_id'] = $scheme_id;
		$pending_account['AccountNumber'] = 'new_account '.$this->api->currentBranch->id.date('YmdHis').rand(1000,9999);
		$pending_account['branch_id'] = $branch->id;
		$pending_account['created_at'] = $created_at;
		$pending_account['LastCurrentInterestUpdatedAt']=isset($otherValues['LastCurrentInterestUpdatedAt'])? :$created_at;

		unset($otherValues['member_id']);
		unset($otherValues['scheme_id']);
		unset($otherValues['AccountNumber']);
		unset($otherValues['branch_id']);
		unset($otherValues['created_at']);
		unset($otherValues['LastCurrentInterestUpdatedAt']);

		foreach ($otherValues as $field => $value) {
			$pending_account[$field] = $value;
		}

		$extra_info=array();
		
		$joint_members=array();
		for($k=2;$k<=4;$k++) {
		    if($j_m_id=$otherValues['member_ID'.$k])
		    	$joint_members[] = $j_m_id;
		}

		$documents=$this->add('Model_Document');
		$documents_feeded = array();
		foreach ($documents as $d) {
		 	if($form[$this->api->normalizeName($documents['name'])]){
				$documents_feeded[$documents['name']]=$form[$this->api->normalizeName($documents['name'].' value')];
		 	}
		}

		$extra_info['joint_members'] = $joint_members;
		$extra_info['documents_feeded'] = $documents_feeded;
		$extra_info['loan_from_account'] = $otherValues['loan_from_account'];
		$extra_info['sm_amount'] = $otherValues['sm_amount'];
		
 		$pending_account['extra_info'] = json_encode($extra_info);
		$pending_account->save();

		return $pending_account;
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=array(),$form=null, $on_date = null ){

		// AccountNumber is already comming as getNewAccountNumber from pending Account Actions.

		if(!$on_date) $on_date = $this->api->now;

		if($otherValues['LoanAgainstAccount_id']){
			$security_account = $this->add('Model_Account')->load($otherValues['LoanAgainstAccount_id']);
			$security_account->lock();
		}

		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$on_date);
		
		// old function name "createProcessingFeeTransaction"
		$this->createProcessingFeeAndSMTransaction($otherValues, $on_date);
		
		$extra_info = json_decode($otherValues['extra_info'],true);

		if($form)
			$this->addDocumentDetails($form);
		else
			$this->addDocumentDetailsFromPending($extra_info);

		if(isset($extra_info['guarantors'])){
			$guarantors = $extra_info['guarantors'];
			foreach ($guarantors as $g) {
				$this->addGuarantor($g['id']);
			}
		}
		
		$this->createPremiums();
	}

	function createProcessingFeeAndSMTransaction($otherValues, $on_date){
		$from_account = $otherValues['loan_from_account'];
		$sm_amount = $otherValues['sm_amount'];

		$scheme = $this->ref('scheme_id');
		$ProcessingFees = $scheme['ProcessingFees'];
		$AccountCredit = $this['Amount'] - $ProcessingFees;
		
		if($scheme['ProcessingFeesinPercent']){
			$ProcessingFees = $this['Amount']* $ProcessingFees / 100;
			$AccountCredit = $this['Amount'] - $ProcessingFees;
		}

		$narration = "Loan Account Openned ". $this['AccountNumber'];
		if($sm_amount){
			$sm_account = $this->add('Model_Account_SM')->addCondition('member_id',$this['member_id'])->setLimit(1)->tryLoadAny();			
			$narration .= " / & Amount Deposited in SM Account " . $sm_account['AccountNumber'];
		} 

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_LOAN_ACCOUNT_OPEN,$this->ref('branch_id'),$on_date, $narration , null, array('reference_id'=>$this->id));
		
		$loan_from_other_account = $this->add('Model_Account')->load($from_account);

		$transaction->addDebitAccount($this, $this['Amount']);
		$transaction->addCreditAccount($this['branch_code'] . SP . PROCESSING_FEE_RECEIVED . SP. $this['scheme_name'], $ProcessingFees);
		if($sm_amount){
			$transaction->addCreditAccount($sm_account, $sm_amount);
			$AccountCredit = $AccountCredit - $sm_amount;
		}
		$transaction->addCreditAccount($loan_from_other_account, $AccountCredit);

		$transaction->execute();
	}

	function addDocumentDetails($form){
		$documents=$this->add('Model_Document');
		foreach ($documents as $d) {
		 	if($form[$this->api->normalizeName($documents['name'])])
		 		$this->updateDocument($documents, $form[$this->api->normalizeName($documents['name'].' value')]);
		}
	}

	function addGuarantor($member_id){
		$g=$this->add('Model_AccountGuarantor');
		$g['account_id'] = $this->id;
		$g['member_id'] = $member_id;
		$g->save();
		return $g;
	}


	function addDocumentDetailsFromPending($extra_info){

		if(!isset($extra_info['documents_feeded'])) return;
		// echo "<pre>";
		// print_r($extra_info);
		// echo "</pre>";
		$doc_info = $extra_info['documents_feeded'];
		foreach ($doc_info as $doc_name => $value) {
			if ($doc_name =="") continue;
			$document = $this->add('Model_Document')->loadBy('name',$doc_name);
			$this->updateDocument($document, $value);
		}
	}

	function getFirstEMIDate($return_date_string=false){
		// ??? .... $this['created_at'] with dealer_monthly_date ... relation

		$date = new MyDateTime($this['created_at']);

		$toAdd = 'P1M';

		if($this['dealer_id']){
			$dd=explode(",",$this['dealer_monthly_date']);
			$applicable_date = (int)date('d',strtotime($this['created_at']));
			if(count($dd)>0){
				foreach ($dd as $dealer_date) {
					if((int)$dealer_date >= (int)date('d',strtotime($this['created_at']))){
						$applicable_date = $dealer_date; 
						break;
					}
				}
				$date = new MyDateTime(date('Y-m-'.$applicable_date,strtotime($this['created_at'])));
				$date->add(new DateInterval($toAdd));
				return $date;
			}
		}
		$date->add(new DateInterval($toAdd));
		if($return_date_string)
			return $date->format('Y-m-d');
		else
			return $date;
	}

	function createPremiums(){
		if(!$this->loaded()) throw $this->exception('Account Must Be loaded to create premiums');
		
		$scheme = $this->ref('scheme_id');

		switch ($scheme['PremiumMode']) {
            case RECURRING_MODE_YEARLY:
                $toAdd = "P1Y";
                break;
            case RECURRING_MODE_HALFYEARLY:
                $toAdd = "P6M";
                break;
            case RECURRING_MODE_QUATERLY:
                $toAdd = "P3D";
                break;
            case RECURRING_MODE_MONTHLY:
                $toAdd = "P1M";
                break;
            case RECURRING_MODE_DAILY:
                $toAdd = "P1D";
                break;
        }

        $date_obj = $this->getFirstEMIDate();
        $lastPremiumPaidDate = $date_obj->format('Y-m-d');
        

        $rate = $scheme['Interest'];
        $premiums = $scheme['NumberOfPremiums'];
        if ($scheme['ReducingOrFlatRate'] == REDUCING_RATE) {
		//          FOR REDUCING RATE OF INTEREST
            $emi = ($this('Amount') * ($rate / 1200) / (1 - (pow(1 / (1 + ($rate / 1200)), $premiums))));
        } else {
		//          FOR FLAT RATE OF INTEREST
            $emi = (($this['Amount'] * $rate * ($premiums + 1)) / 1200 + $this['Amount']) / $premiums;
        }
        $emi = round($emi);
        
        $prem = $this->add('Model_Premium');
        for ($i = 1; $i <= $premiums ; $i++) {
            $prem['account_id'] = $this->id;
            $prem['Amount'] = $emi;
            if($i!=1) // First Emi is already a month ahead
	            $date_obj->add(new DateInterval($toAdd));
            $lastPremiumPaidDate = $prem['DueDate'] = $date_obj->format('Y-m-d');
            $prem->saveAndUnload();
        }
	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null,$on_date=null,$in_branch=null){
		if(!$on_date) $on_date = $this->api->now;

		parent::deposit($amount,$narration,$accounts_to_debit,$form,$on_date,$in_branch);

		$this->payPremiums($amount,$on_date);
		// $this->closeIfPaidCompletely();
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null,$in_branch=null){
		throw $this->exception('Withdrawl not supported in loan accounts', 'ValidityCheck')->setField('AccountNumber');
		// parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date,$in_branch);
	}

	function payPremiums($amount,$on_date){
		$PaidEMI = $this->ref('Premium')->addCondition('Paid','<>',0)->count()->getOne();
		$emi = $this->ref('Premium')->tryLoadAny()->get('Amount');
		$rate = $this->ref('scheme_id')->get('Interest');
		$premiums = $this->ref('scheme_id')->get('NumberOfPremiums');

		$tran_penalty_and_other_amt_received = $this->add('Model_TransactionRow')->addCondition('transaction_type',[TRA_PENALTY_AMOUNT_RECEIVED,TRA_OTHER_AMOUNT_RECEIVED])->addCondition('account_id',$this->id)->sum('amountCr')->getOne();

		// Access amount then loan amount per premium is actually interest
		$interest = round((($emi * $premiums) - $this['Amount']) / $premiums);

		$PremiumAmountAdjusted = $PaidEMI * $emi;
		$AmountForPremiums = ($this['CurrentBalanceCr']- $tran_penalty_and_other_amt_received) - $PremiumAmountAdjusted; // This amount is already credit to account in deposit function parent::deposit 

		$premiumsSubmited = (int) ($AmountForPremiums / $emi);

		if ($premiumsSubmited > 0) {
			$prem = $this->ref('Premium')->addCondition('Paid',false)->setOrder('id')->setLimit($premiumsSubmited);
		    foreach ($prem as $prem_array) {
		        $prem['PaidOn'] = $on_date;
		        $prem['Paid'] = true;
		        $prem->saveAndUnload();
		    }
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

	    $interest = round($interest,0);

	    // $interest = interest value for one premium

	    $transaction = $this->add('Model_Transaction');
	    $transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_LOAN,$this->ref('branch_id'),$on_date, "Interest posting in Loan Account ".$this['AccountNumber'],null, array('reference_id'=>$this->id));
	    
	    $transaction->addDebitAccount($this, $interest);
	    $transaction->addCreditAccount($this->ref('branch_id')->get('Code') . SP . INTEREST_RECEIVED_ON . SP. $this['scheme_name'], $interest);
	    
	    $transaction->execute();
	}

	function postPanelty($on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		if(!$this->hasElement('due_panelty')) throw $this->exception('The Account must be called via scheme daily function');

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT,$this->ref('branch_id'), $on_date, "Penalty deposited on Loan Account for ".date("F",strtotime($on_date)), null, array('reference_id'=>$this->id));

		$amount = $this['due_panelty'];
		
		$transaction->addDebitAccount($this, $amount);
		$transaction->addCreditAccount($this->ref('branch_id')->get('Code') . SP . PENALTY_DUE_TO_LATE_PAYMENT_ON . SP.  $this['scheme_name'], $amount);
		
		$transaction->execute();

		// Make all penaltyPosted = penaltyCharged

		$premiums = $this->add('Model_Premium');
		$premiums->addCondition('account_id',$this->id);
		$premiums->_dsql()->set('PaneltyPosted',$this->dsql()->expr('PaneltyCharged'));
		$premiums->_dsql()->update();

	}

	function getVehicalNo(){
		$doc = $this->add('Model_Document');
		$doc->addCondition('name','VEHICLE NO.');

		if(!$this->loaded() or !$doc->count()->getOne())
			return "Not Found";

		$doc->tryLoadAny();
		return $this->getDescription($doc->id);
	}

	function getChassisNo(){
		$doc = $this->add('Model_Document');
		$doc->addCondition('name','CHASSIS NO.');

		if(!$this->loaded() or !$doc->count()->getOne())
			return "Not Found";

		$doc->tryLoadAny();
		return $this->getDescription($doc->id);	
	}

	function getEngineNo(){
		$doc = $this->add('Model_Document');
		$doc->addCondition('name','ENGINE NO.');

		if(!$this->loaded() or !$doc->count()->getOne())
			return "No Found";

		$doc->tryLoadAny();
		return $this->getDescription($doc->id);
	}

	function getDescription($doc_id){
		$doc_sub_model = $this->add('Model_DocumentSubmitted');
		$doc_sub_model->addCondition('accounts_id',$this->id);
		$doc_sub_model->addCondition('documents_id',$doc_id);
		
		if($doc_sub_model->count()->getOne()){
			$doc_sub_model->tryLoadAny();
			return $doc_sub_model['Description'];
		}else
			return "Not Found";

	}

}