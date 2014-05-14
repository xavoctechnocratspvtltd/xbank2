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


	}
}