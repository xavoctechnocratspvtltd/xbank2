<?php
class Model_Premium extends Model_Table {
	var $table= "premiums";
	function init(){
		parent::init();


		$this->hasOne('Account','account_id');
		$this->addField('Amount');
		$this->addField('Paid');//->type('boolean')->defaultValue(false);
		$this->addField('Skipped')->type('boolean')->defaultValue(false);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('PaidOn')->type('datetime')->defaultValue(null);
		$this->addField('AgentCommissionSend')->type('boolean')->defaultValue(false);
		$this->addField('AgentCommissionPercentage')->type('money');
		$this->addField('PaneltyCharged')->type('money');
		$this->addField('PaneltyPosted')->type('money');
		$this->addField('DueDate')->type('date');

		$this->addExpression('panelty_to_post')->set('PaneltyCharged - PaneltyPosted');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function payNowForRecurring($on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		
		$this['PaidOn'] = $on_date;
		$this->save();
		throw $this->exception('What is my Paid value');

		$this->giveAgentCommission($on_date);
	}

	function giveAgentCommission($on_date = null){
		if(!$on_date) $on_date = $this->api->now;

		$all_paid_noncommissioned_preimums = $this->ref('account_id')->ref('Premiums');
		$all_paid_noncommissioned_preimums->addCondition('Paid',true);
		$all_paid_noncommissioned_preimums->addCondition('AgentCommissionSend',false);

		$commission = 0;

		foreach($all_paid_noncommissioned_preimums as $junk){
			$commission = $commission + ($all_paid_noncommissioned_preimums['Amount'] * $all_paid_noncommissioned_preimums['AgentCommissionPercentage'] / 100.00);
			$all_paid_noncommissioned_preimums['AgentCommissionSend'] = true;
			$all_paid_noncommissioned_preimums->saveAndUnload();			
		}

		$account = $this->ref('account_id');

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction(TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT, $account->ref('branch_id'), $on_date, "RD Premium Commission ".$account['AccountNumber'], null, array('reference_account_id'=>$account->id));
		
		$transaction->addDebitAccount($account->ref('branch_id')->get('Code') . SP . COMMISSION_PAID_ON . $account['scheme_name'] , $commission);
		$transaction->addCreditAccount($account->ref('agent_id')->ref('account_id'), $commission);
		
		$transaction->execute();

	}

	function getAllForPaneltyPosting($on_date=null){

		$this->getElement('account_id')->destroy();

		if(!$on_date) $on_date = $this->api->now;

		$loan_premiums = $this;
		$loan_premiums->dsql()->del('fields');//->fieldQuery('sum(PaneltyCharged - PaneltyPosted )');

		$account_join = $loan_premiums->join('accounts','account_id');
		$scheme_join = $account_join->join('schemes','scheme_id');

		$account_join->addField('ActiveStatus');
		$scheme_join->addField('SchemeType');

		$loan_premiums->addCondition('ActiveStatus',true);
		$loan_premiums->addCondition('SchemeType',ACCOUNT_TYPE_LOAN);
		$loan_premiums->addCondition('PaneltyCharged','<>',$this->dsql()->expr('PaneltyPosted'));
		

		$loan_premiums->_dsql()->debug()->group('account_id');

		return $this;
	}

}