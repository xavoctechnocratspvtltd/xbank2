<?php

class page_tests_025AccountFunctions extends Page_Tester {
    public $title = 'Account Function Testing';

    public $account;
    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_checkCC_Interest_1'=>80.26,
        'Test_dds_maturity_date_1'=>'2014-12-31',
        'Test_dds_maturity_date_2'=>'2015-03-31',
        'Test_dds_maturity_date_3'=>'2015-02-28',
    );

    function prepare(){
    	$this->account = $this->add('Model_Account_CC');
        return null;
    }

    function test_checkCC_Interest_1(){
    	// return round($this->account->getCCInterest($on_date='2014-06-25',$after_date='2014-06-04',$on_amount='7750', $at_interest_rate='18'),2);
    }

    function test_dds_maturity_date_1(){
        $created_at='2014-01-01';
        $account = $this->add('Model_Account_DDS');
        $account->createNewAccount(1035,81,$this->api->current_branch, 'UDRDDS'.rand(1000,9999),$otherValues=array('Amount'=>3000),$form=null,$created_at);
        return $account['maturity_date'];
    }

    function test_dds_maturity_date_2(){
        $created_at='2014-04-01';
        $this->account = $account = $this->add('Model_Account_DDS');
        $account->createNewAccount(1035,81,$this->api->current_branch, 'UDRDDS'.rand(1000,9999),$otherValues=array('Amount'=>3000),$form=null,$created_at);
        return $account['maturity_date'];
    }

    function test_dds_maturity_date_3(){
        $created_at='2014-03-01';
        $this->account = $account = $this->add('Model_Account_DDS');
        $account->createNewAccount(1035,81,$this->api->current_branch, 'UDRDDS'.rand(1000,9999),$otherValues=array('Amount'=>3000),$form=null,$created_at);
        return $account['maturity_date'];
    }

}