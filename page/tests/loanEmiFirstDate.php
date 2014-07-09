<?php

class page_tests_loanEmiFirstDate extends Page_Tester {
    public $title = 'Loan Emi First Date';

    public $proper_responses=array(
        "Test_check1"=>'2014-02-10',
    );

    function test_check1(){
        $acc=$this->add('Model_Account_Loan');
        $acc['dealer_id']=0;
        $acc['dealer_monthly_date'] = 15;
        $acc['created_at']='2014-01-10';
        return $acc->getFirstEMIDate();
    }

}