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
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".MaturityPeriod MONTH)";
		});

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null){
		throw new Exception("Check For Premiums and Commissions etc first", 1);
		parent::deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null);
		
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null){
		if(!$this->isMatured() OR !$this->isActive())
			throw $this->exception('Account must be matured or deactivated to withdraw','ValidityCheck')->setField('account');

		if($amount != ($amount_to_withdraw = ($this['CurrentBalanceCr'] - $this['CurrentBalanceDr'])))
			throw $this->exception('You have to withdraw '.$amount_to_withdraw .' /-','ValidityCheck')->setField('amount');

		parent::withdrawl($amount,$narration,$accounts_to_credit,$form,$on_date);
	}

	function giveAgentCommission(){
		if(!$this['agent_id']) return;

		$monthDifference = $this->api->my_date_diff($this->api->today, $this['created_at']);
        $monthDifference = $monthDifference["months_total"]+1;
        $percent = explode(",", $this->ref('scheme_id')->get('AccountOpenningCommission'));
        $percent = (isset($percent[$monthDifference])) ? $percent[$monthDifference] : $percent[count($percent) - 1];
        $amount = $amount * $percent /100;
        $agentAccount = $this->ref('agent_id')->get('AccountNumber');

        $transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,null,null,"DDS Premium Commission",null,array('reference_account_id'=>$this->id));

        $comm_acc = $this->ref('branch_id')->get('Code') . SP . COMMISSION_PAID_ON . $this->ref('scheme_id')->get('name');
		$transaction->addDebitAccount($comm_acc,$amount);

		$transaction->addCreditAccount($this->ref('agent_id')->ref('account_id')->get('AccountNumber'),($amount - ($amount * TDS_PERCENTAGE / 100)));
		$transaction->addCreditAccount($this->ref('agent_id')->ref('account_id')->ref('branch_id')->get('Code').SP.BRANCH_TDS_ACCOUNT,($amount * TDS_PERCENTAGE / 100));

		$transaction->execute();
	}

	function markMatured($on_date=null){
		if(!$this->loaded()) throw $this->exception('DDS Account must be loaded to be marked matured');
		if($this->isMatured()) throw $this->exception('DDS Account already matured');
		if(!$this->isActive()) throw $this->exception('DDS Account is de-active...');
		if(!$on_date) $on_date = $this->api->today;

		$days = $this->api->my_date_diff($on_date,$this['LastCurrentInterestUpdatedAt']);
		$days = $days['days_total'];
		$interest = $this['CurrentInterest']= $this['CurrentInterest'] + ($this['CurrentBalanceCr'] * $this['Interest'] * $days / 36500);
		$this['MaturityStatus']=true;
		$this['CurrentInterest'] = 0; // No more needed to store interest in this field

		$this->save();
		if(!$interest) return;

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_INTEREST_POSTING_IN_DDS,null,$on_date,"Interst posting in DDS Account " . $this['AccountNumber'],null, array('reference_account_id'=>$this->id));
		
		$transaction->addDebitAccount($this['Code'] . SP . INTEREST_PAID_ON . $this['scheme_name'], $interest);
		$transaction->addCreditAccount($this, $interest);

		$transaction->execute();
	}
}