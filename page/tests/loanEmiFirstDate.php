<?php

class page_tests_loanEmiFirstDate extends Page_Tester {
    public $title = 'Loan Emi First Date';

    public $proper_responses=array(
        "Test_check1"=>'2014-02-10',
        "Test_check2"=>'2014-02-20',
        "Test_check3"=>'2014-02-28',
        "Test_check4"=>'2014-02-28',
        "Test_check5"=>'2014-04-30',
    );

    function test_check1(){
        $acc=$this->add('Model_Account_Loan');
        $acc['dealer_id']=1;
        $acc['dealer_monthly_date'] = "10,20,30";
        $acc['created_at']='2014-01-05';
        return $acc->getFirstEMIDate()->format('Y-m-d');
    }
    function test_check2(){
        $acc=$this->add('Model_Account_Loan');
        $acc['dealer_id']=1;
        $acc['dealer_monthly_date'] = "10,20,30";
        $acc['created_at']='2014-01-11';
        return $acc->getFirstEMIDate()->format('Y-m-d');
    }
    function test_check3(){
        $acc=$this->add('Model_Account_Loan');
        $acc['dealer_id']=1;
        $acc['dealer_monthly_date'] = "10,20,30";
        $acc['created_at']='2014-01-25';
        return $acc->getFirstEMIDate()->format('Y-m-d');
    }
    function test_check4(){
        $acc=$this->add('Model_Account_Loan');
        $acc['dealer_id']=1;
        $acc['dealer_monthly_date'] = "10,20,30";
        $acc['created_at']='2014-01-31';
        return $acc->getFirstEMIDate()->format('Y-m-d');
    }

    function test_check5(){
        $acc=$this->add('Model_Account_Loan');
        $acc['dealer_id']=1;
        $acc['dealer_monthly_date'] = "10,20,30";
        $acc['created_at']='2014-03-31';
        return $acc->getFirstEMIDate()->format('Y-m-d');
    }

}