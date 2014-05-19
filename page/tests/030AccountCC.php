<?php

class page_tests_030AccountCC extends Page_Tester {
    public $title = 'Account Testing';
    public $account_type = ACCOUNT_TYPE_CC;

    public $proper_responses=array(
        "Test_accountType"=>ACCOUNT_TYPE_CC,
        'Test_CreateAccount'=>0,
        'Test_CheckProcessingFeeEntry'=>0,
        'Test_Deposit_Withdraw_EntrIES'=>0,
    );

    function prepare(){
        return null;
    }

    function test_accountType(){
        return $this->account_type;
    }
}