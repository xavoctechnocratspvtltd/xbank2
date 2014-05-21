<?php

class page_tests_025AccountFunctions extends Page_Tester {
    public $title = 'Account Function Testing';

    public $account;
    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_checkCC_Interest_1'=>80.26,
        'Test_checkCC_Interest_2'=>123,
        'Test_checkCC_Interest_3'=>123,
    );

    function prepare(){
    	$this->account = $this->add('Model_Account_CC');
        return null;
    }

    function test_checkCC_Interest_1(){
    	return round($this->account->getCCInterest($on_date='2014-06-25',$after_date='2014-06-04',$on_amount='7750', $at_interest_rate='18'),2);
    }

    function test_checkCC_Interest_2(){
        return round($this->account->getCCInterest($on_date='2014-05-01',$after_date='2014-04-01',$on_amount='2500', $at_interest_rate='20'),2);
    }

    function test_checkCC_Interest_3(){
        return round($this->account->getCCInterest($on_date='2014-05-01',$after_date='2014-04-01',$on_amount='2500', $at_interest_rate='25'),2);
    }

}