<?php

class page_tests_025AccountFunctions extends Page_Tester {
    public $title = 'Account Function Testing';

    public $account;
    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_checkCC_Interest_1'=>123,
    );

    function prepare(){
    	$this->account = $this->add('Model_Account_CC');
        return null;
    }

    function test_checkCC_Interest_1(){
    	return $this->account->getCCInterest($on_date='2014-05-01',$from_date='2014-04-01',$on_amount='2500', $at_interest_rate='18');
    }

}