<?php
class Model_Account_DDS extends Model_Account{
	
	public $transaction_deposit_type = TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "DDS Amount Deposit in {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','DDS');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','DDS');
		$this->getElement('Amount')->caption('DDS amount (in multiples of Rs.300 like 300, 600, 900....3000 etc.)');

		$this->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod MONTH)";
		});

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=null,$form=null,$created_at=null){
		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);

		if($agent_id = $otherValues['agent_id'])
			$this->addAgent($agent_id, $replace_existing=true);

		if($otherValues['initial_opening_amount'])
			$this->deposit($otherValues['initial_opening_amount'],null,null,$form);
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$transaction_date=null,$in_branch=null){
		
		$given_interest = $this->interestGiven();
		$maturity_months = $this->ref('scheme_id')->get('MaturityPeriod');
		$dds_amount = $this['Amount'];

		$required_amount = (($dds_amount * $maturity_months) + $given_interest) - ($this['CurrentBalanceCr']);
		if($amount > $required_amount)
			throw $this->exception('Exceeding Amount, only required '. $required_amount, 'ValidityCheck')->setField('amount');

		parent::deposit($amount,$narration,$accounts_to_debit,$form,$transaction_date,$in_branch);
		// if($this->ref('agent_id')->loaded()){
		// 	$this->giveAgentCommission($on_amount = $amount, $transaction_date);
		// }
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null){
		if(!$this->isMatured() OR !$this->isActive())
			throw $this->exception('Account must be matured or deactivated to withdraw','ValidityCheck')->setField('account');

		if($amount != ($amount_to_withdraw = ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr'])))
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
	// 	$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,null,$on_date,"DDS Premium Commission",null,array('reference_account_id'=>$this->id));

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

		$this->saveAndUnload();
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
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_DDS,null,$on_date,"Interst posting in DDS Account " . $this['AccountNumber'],null, array('reference_account_id'=>$this->id));
		
		$transaction->addDebitAccount($this['branch_code'] . SP . INTEREST_PAID_ON . SP. $this['scheme_name'], $interest);
		$transaction->addCreditAccount($this, $interest);

		$transaction->execute();
	}

	function interestGiven($from_date=null,$to_date=null){
		$transaction_row = $this->add('Model_TransactionRow');
		$transaction_join = $transaction_row->join('transactions','transaction_id');
		$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
		$transaction_type_join->addField('_transaction_type','name');

		$transaction_row->addCondition('_transaction_type',TRA_INTEREST_POSTING_IN_DDS);


		if($from_date)
			$transaction_row->addCondition('created_at','>=',$from_date);
		if($to_date)
			$transaction_row->addCondition('created_at','<',$this->api->nextDate($from_date));

		return $transaction_row->sum('amountCr')->getOne();

	}

	function postAgentCommissionEntry($on_date=null, $return=false){
        $agentAccount = $this->ref('agent_id')->ref('account_id');
        $branch_code =$this->ref('branch_id')->get('Code');

        $tds_percentage = $this->ref('agent_id')->ref('member_id')->hasPanNo()? 10 : 20 ;
//            $amount = $ac->amountCr;

        $this->interestGiven();

//------------CALCULATING COMMISSION FOR DDS----------------
        $DA = $this['Amount']; // DA => Monthly DDS Amount

        //  VVV--  Set in monthly closing scheme file monthly_credited_amount
        $x = $this['monthly_credited_amount']; // x => Amount Submitted in the current month
        $tA = $this['CurrentBalanceCr'] - $x; // tA => Total amount till date given excluding x ???? Interest Given Included ????

        while($x > 0){
            $y = $DA- ($tA - ((int)($tA / $DA)) * $DA);
            $z = $tA / $DA;
            $old = ($x / $DA > 1 ? $y : $x);

            $percent = explode(",", $this['AccountOpenningCommission']);
            $percent = (isset($percent[$z])) ? $percent[$z] : $percent[count($percent) - 1];
            $amount = $old * $percent / 100;

            $transaction = $this->add('Model_Transaction');
            $transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $this->ref('branch_id') , $on_date, "DDS Premium Commission ".$this['AccountNumber'], $only_transaction=false, array('reference_account_id'=>$this->id));
            
            $transaction->addDebitAccount($branch_code. SP . COMMISSION_PAID_ON . SP. $this['scheme_name'], $amount);
            $transaction->addCreditAccount($agentAccount ,($amount - ($amount * TDS_PERCENTAGE / 100)));
            $transaction->addCreditAccount($branch_code.SP.BRANCH_TDS_ACCOUNT ,(($amount * TDS_PERCENTAGE / 100)));
            
            $transaction->execute();

            $x = $x - $old;
            $tA = $tA + $old;
        }

	}
}