<?php
class Model_Account_DDS extends Model_Account{
	
	public $transaction_deposit_type = TRA_DDS_ACCOUNT_AMOUNT_DEPOSIT;	
	public $default_transaction_deposit_narration = "DDS Amount Deposit in {{AccountNumber}}";	

	function init(){
		parent::init();

		$this->addCondition('SchemeType','DDS');
		$this->getElement('scheme_id')->getModel()->addCondition('SchemeType','DDS');
		$this->getElement('Amount')->caption('DDS amount (in multiples of Rs.300 like 300, 600, 900....3000 etc.)');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null){
		throw new Exception("Check For Premiums and Commissions etc first", 1);
		parent::deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null);
		
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
		$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT,null,null,"DDS Premium Commission");

        $voucherNo = array('voucherNo' => Transaction::getNewVoucherNumber(), 'referanceAccount' => $ac->id);
        $debitAccount = array(
            Branch::getCurrentBranch()->Code . SP . COMMISSION_PAID_ON . $ac->Schemes->Name => $amount,
        );
        $creditAccount = array(
            // get agents' account number
//                                            Branch::getCurrentBranch()->Code."_Agent_SA_". $ac->Agents->member_id  => ($amount  - ($amount * 10 /100)),
            $agentAccount => ($amount - ($amount * TDS_PERCENTAGE / 100)),
            Account::getAccountForCurrentBranch(BRANCH_TDS_ACCOUNT)->AccountNumber => ($amount * TDS_PERCENTAGE / 100),
        );
        Transaction::doTransaction($debitAccount, $creditAccount, "DDS Premium Commission", TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $voucherNo);
	}
}