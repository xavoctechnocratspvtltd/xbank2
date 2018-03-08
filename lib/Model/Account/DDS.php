<?php
class Model_Account_DDS extends Model_Account{
	
	public $transaction_deposit_type = TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "DDS Amount Deposit in {{AccountNumber}} ({{AccountHolderName}})";	

	public $transaction_withdraw_type = TRA_DDS_ACCOUNT_AMOUNT_WITHDRAWL;	
	public $default_transaction_withdraw_narration = "Amount withdrawl from {{SchemeType}} Account {{AccountNumber}}";

	function init(){
		parent::init();

		$this->addCondition('SchemeType','DDS');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','DDS');
		$this->getElement('Amount')->caption('Daily Deposit Amount')->type('number');
		$this->getElement('account_type')->defaultValue(ACCOUNT_TYPE_DDS);
		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod MONTH)";
		});

		$this->addExpression('locked_to_loan_till')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".no_loan_on_deposit_till MONTH)";
		});

		$this->scheme_join->addField('percent_loan_on_deposit');
		$this->scheme_join->addField('no_loan_on_deposit_till');
		$this->scheme_join->addField('dds_type','type');
		// $this->addCondition('dds_type','DDS');


		// $this->addHook('afterAccountDebited,afterAccountCredited',array($this,'closeIfPaidCompletely'));

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=null,$form=null,$created_at=null){
		if(!$AccountNumber) $AccountNumber = $this->getNewAccountNumber();
		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);

		if($agent_id = $otherValues['agent_id']){
			$this->addAgent($agent_id, $replace_existing=true);
			$this->agent()->addCRPB($this->scheme()->get('CRPB'), $this['Amount'] * 30);
		}

		if($otherValues['initial_opening_amount']){
			$this->deposit($otherValues['initial_opening_amount'],null,$otherValues['debit_account']?:null,$form);

			$member=$this->add('Model_Member')->load($member_id);
			$msg="Dear Member, Your account ".$AccountNumber." has been opened with amount ".$otherValues['initial_opening_amount']." on dated ". $this->app->today. " From:- Bhawani Credit Co-Operative Society Ltd. +91 8003597814";
			
			$mobile_no=explode(',', $member['PhoneNos']);
			if(strlen(trim($mobile_no[0])) == 10){
				$sms=$this->add('Controller_Sms');
				$sms->sendMessage($mobile_no[0],$msg);
			}

			return;
		}
		// throw new \Exception("Error Processing Request". print_r($otherValues,true), 1);
		
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$transaction_date=null,$in_branch=null){
		
		if($this->isActive())

		$given_interest = $this->interestGiven();
		$maturity_months = $this->ref('scheme_id')->get('MaturityPeriod');
		$dds_amount = $this['Amount'];

		$required_amount = (($dds_amount * $maturity_months * 30) + $given_interest) - ($this['CurrentBalanceCr']);

		if($amount > $required_amount)
			throw $this->exception('Exceeding Amount, only required '. $required_amount, 'ValidityCheck')->setField('amount');

		parent::deposit($amount,$narration,$accounts_to_debit,$form,$transaction_date,$in_branch);
		// AGENT COMMISSION SET TO DAILY ON DAY WHEN ACCOUNT HAS COMPLETED 30 DAYS
		// if($this->ref('agent_id')->loaded()){
		// 	$this->giveAgentCommission($on_amount = $amount, $transaction_date);
		// }
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null){
		if(!$this->isMatured() OR $this->isActive())
			throw $this->exception('Account must be matured or deactivated to withdraw','ValidityCheck')->setField('account');

		if($amount != ($amount_to_withdraw = round(($this['CurrentBalanceCr'] - $this['CurrentBalanceDr']),0)))
			throw $this->exception('You have to withdraw '.$amount_to_withdraw .' /-','ValidityCheck')->setField('amount');

		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}

	// AGENT COMMISSION FOR DDS IS SHIFTED TO MONTHLY
	// function giveAgentCommission($on_amount,$on_date){
	// 	if(!$this['agent_id']) return;

	// 	$monthDifference = $this->api->my_date_diff($on_date, $this['created_at']);
 //        $monthDifference = $monthDifference["months_total"]+1;
 //        $percent = explode(",", $this->ref('scheme_id')->get('AccountOpenningCommission'));
 //        $percent = (isset($percent[$monthDifference])) ? $percent[$monthDifference] : $percent[count($percent) - 1];
 //        $amount = $on_amount * $percent /100;
 //        $agentAccount = $this->ref('agent_id')->ref('agent_id')->get('AccountNumber');

 //        $transaction = $this->add('Model_Transaction');
	// 	$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,null,$on_date,"DDS Premium Commission",null,array('reference_id'=>$this->id));

 //        $comm_acc = $this->ref('branch_id')->get('Code') . SP . COMMISSION_PAID_ON . $this->ref('scheme_id')->get('name');
	// 	$transaction->addDebitAccount($comm_acc,$amount);

	// 	$transaction->addCreditAccount($this->ref('agent_id')->ref('account_id')->get('AccountNumber'),($amount - ($amount * TDS_PERCENTAGE / 100)));
	// 	$transaction->addCreditAccount($this->ref('agent_id')->ref('account_id')->ref('branch_id')->get('Code').SP.BRANCH_TDS_ACCOUNT,($amount * TDS_PERCENTAGE / 100));

	// 	$transaction->execute();
	// }

	function markMatured($on_date=null){
		if(!$this->loaded()) throw $this->exception('DDS Account must be loaded to be marked matured');
		if($this->isMatured()) throw $this->exception('DDS Account already matured');
		if(!$this->isActive()) throw $this->exception('DDS Account is de-active...');
		if(!$on_date) $on_date = $this->api->today;

		$this['MaturedStatus']=true;
		$this['CurrentInterest'] = 0; // No more needed to store interest in this field
		
		$this->_dsql()->del('limit');

		$this->saveAs('Model_Account');
	}

	function postInterestEntry($on_date){
		$days = $this->api->my_date_diff($on_date,$this['LastCurrentInterestUpdatedAt']);
		$days = $days['days_total'];
		// echo " (".$this['CurrentBalanceCr']. " * ".$this['Interest']." / 1200) - ".$this->interestGiven() ." <br/>";
		$interest = (($this['CurrentBalanceCr'] - $this->interestGiven()) * $this['Interest'] / 1200)-$this->interestGiven() ;

		$this['LastCurrentInterestUpdatedAt'] = $on_date;

		$this->_dsql()->del('limit');
		$this->save();

		if(!$interest) return;

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_DDS,$this->ref('branch_id'),$on_date,"Interst posting in DDS Account " . $this['AccountNumber'],null, array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'], $interest);
		$transaction->addCreditAccount($this, $interest);

		// throw new \Exception($this->ref('branch_id')->get('name'), 1);
		

		$transaction->execute();
	}

	function interestGiven($from_date=null,$to_date=null){
		$transaction_row = $this->add('Model_TransactionRow');
		// $transaction_join = $transaction_row->join('transactions','transaction_id');
		// $transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
		// $transaction_type_join->addField('x_transaction_type','name');

		$transaction_row->addCondition('transaction_type',TRA_INTEREST_POSTING_IN_DDS);
		$transaction_row->addCondition('account_id',$this->id);

		if($from_date)
			$transaction_row->addCondition('created_at','>=',$from_date);
		if($to_date)
			$transaction_row->addCondition('created_at','<',$this->api->nextDate($from_date));

		return $transaction_row->sum('amountCr')->getOne();

	}

	function postAgentCommissionEntry($on_date=null, $return=false){
        $agentAccount = $this->ref('agent_id')->ref('account_id');
        $branch_code =$this->ref('branch_id')->get('Code');

        $tds_percentage = $this->ref('agent_id')->ref('member_id')->hasPanNo()? TDS_PERCENTAGE_WITH_PAN : TDS_PERCENTAGE_WITHOUT_PAN ;
//            $amount = $ac->amountCr;

        $int_amt = $this->interestGiven($this->api->previousMonth($on_date),$this->api->nextDate($on_date));

        $months_diff = $this->api->my_date_diff($this->api->nextDate($on_date),$this['created_at']);
		$months_diff = $months_diff['months_total'];
		$percent = $this->api->getComission($this->scheme()->get('AccountOpenningCommission'), PREMIUM_COMMISSION, $months_diff);

		$monthly_submitted_amount = $this->creditedAmount($this->api->previousMonth($on_date),$on_date);

		$monthly_submitted_amount = $monthly_submitted_amount - $int_amt;

        $amount = $monthly_submitted_amount * $percent / 100;
        
        $commissionForThisAgent = round($this->agent()->cadre()->selfEfectivePercentage() * $amount / 100.00,2);
        $tds = round($commissionForThisAgent * $tds_percentage / 100,2);

        $transaction = $this->add('Model_Transaction');
        $transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $this->ref('branch_id') , $on_date, "DDS Premium Commission ".$this['AccountNumber'], $only_transaction=false, array('reference_id'=>$this->id));
        
        $transaction->addDebitAccount($branch_code. SP . COMMISSION_PAID_ON . SP. $this['scheme_name'], $commissionForThisAgent);
        $transaction->addCreditAccount($agentAccount ,($commissionForThisAgent - $tds));
        $transaction->addCreditAccount($branch_code.SP.BRANCH_TDS_ACCOUNT ,$tds);
        
        $transaction->execute();

        $this->propogateAgentCommission($this['branch_code'] . SP . COMMISSION_PAID_ON . SP. $this['scheme_name'], $total_commission_amount = $amount, $on_date);
	}

	function giveCollectionCharges($on_date=null){

		if(!$on_date) $on_date = $this->api->today;

		// echo $this['AccountNumber']. ' '. $this->agent()->get('name') .' ' .  $this->agent()->account()->get('AccountNumber') . '<br/>';
		$agentAccount = $this->agent()->account();
		
		if(!$agentAccount) return;

        $branch_code =$this->ref('branch_id')->get('Code');

        $tds_percentage = $this->ref('agent_id')->ref('member_id')->hasPanNo()? TDS_PERCENTAGE_WITH_PAN : TDS_PERCENTAGE_WITHOUT_PAN ;
//            $amount = $ac->amountCr;

        $int_amt = $this->interestGiven($this->api->previousMonth($on_date),$this->api->nextDate($on_date));

        $months_diff = $this->api->my_date_diff($this->api->nextDate($on_date),$this['created_at']);
		$months_diff = $months_diff['months_total'];
		$percent = $this->api->getComission($this->scheme()->get('CollectorCommissionRate'), PREMIUM_COMMISSION, $months_diff);

		$monthly_submitted_amount = $this->creditedAmount($this->api->previousMonth($on_date),$on_date);

		$monthly_submitted_amount = $monthly_submitted_amount - $int_amt;

        $amount = $monthly_submitted_amount * $percent / 100;
        
        $commissionForThisAgent = round($amount,2);
        $tds = round(($commissionForThisAgent * $tds_percentage / 100),2);

        $transaction = $this->add('Model_Transaction');
        $transaction->createNewTransaction(TRA_PREMIUM_AGENT_COLLECTION_CHARGE_DEPOSIT, $this->ref('branch_id') , $on_date, "DDS Premium Collection Charge ".$this['AccountNumber'], $only_transaction=false, array('reference_id'=>$this->id));
        
        $transaction->addDebitAccount($branch_code. SP . COLLECTION_CHARGE_PAID_ON . SP. $this['scheme_name'], $commissionForThisAgent);
        $transaction->addCreditAccount($agentAccount ,$commissionForThisAgent - $tds);
        $transaction->addCreditAccount($branch_code.SP.BRANCH_TDS_ACCOUNT ,$tds);
        
        $transaction->execute();
	}
}