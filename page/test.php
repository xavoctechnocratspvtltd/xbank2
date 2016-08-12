<?php



class page_test  extends Page {
	function init(){
		parent::init();

		$a =$this->add('Model_Account_FixedAndMis')->loadBy('AccountNumber','OGNFD389');
		echo $a->getAmountForInterest($this->app->nextDate($this->app->today),true);
		// exit;
	}
}
