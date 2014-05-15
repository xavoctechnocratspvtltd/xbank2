<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
	
		$accounts  = explode(',',ACCOUNT_TYPES);
		foreach($accounts as $acc){
			$this->add('View')->set($acc. ' = '. $this->add('Model_Account_'.$acc)->count()->getOne());
			$this->add('View')->set($acc. ' === '. $this->add('Model_Account_'.$acc)->tryLoadAny()->ref('scheme_id')->ref('Account')->count()->getOne());
		}

		$panelty_accounts = $this->add('Model_Account_Loan');
		$premium_join = $panelty_accounts->leftJoin('premiums.account_id');
		$premium_join->addField('DueDate');
		$premium_join->addField('Paid');
		$dealer_join = $panelty_accounts->leftJoin('dealers','dealer_id');
		$dealer_join->addField('loan_panelty_per_day');

		$panelty_accounts->addCondition('DueDate','<',$this->api->today);
		$panelty_accounts->addCondition('Paid',false);

		$panelty_accounts->_dsql()->set('CurrentInterest',$this->api->db->dsql()->expr('CurrentInterest +'. $dealer_join->table_alias.'.loan_panelty_per_day'));
		$panelty_accounts->_dsql()->update();
		
	}
}