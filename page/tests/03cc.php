<?php

class page_tests_03cc extends Page_Tester {
    public $title = 'CC Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_interest'=>array('UDRCC001'=>123,'UDRCC002'=>123,'UDRCC003'=>123,'UDRCC005'=>123,),
    );

    function prepare(){
        return null;
    }

    function prepare_interest(){
        return array($this->api->today);
    }

    function test_interest($till_date){
        $result =array();
        foreach($this->proper_responses['Test_interest'] as $account=>$accurate_interest){
            $a=$this->add('Model_Account_CC');
            $a->loadBy('AccountNumber',$account);
            $result += array(
                    $account=>$a->applyMonthlyInterest($till_date,true)
                );

        }
        return $result;
    }

}