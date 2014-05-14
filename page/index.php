<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
	
		$accounts  = explode(',',ACCOUNT_TYPES);
		foreach($accounts as $acc){
			$this->add('View')->set($acc. ' = '. $this->add('Model_Account_'.$acc)->count()->getOne());
		}

		$this->add('Model_Scheme_Loan')->daily();

	}
}