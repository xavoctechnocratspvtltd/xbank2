<?php

class page_tests_voucherno extends Page_Tester {
    public $title = 'Voucher Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_Next'=>array('UDP'=>1,'JHD'=>80),
        'Test_BackDate'=>array('UDP'=>1,'JHD'=>80),
    );

    function prepare(){
        return null;
    }

    // function prepare_Next(){
    //     return array($branch->id);
    // }

    function test_Next(){
        $branches = $this->add('Model_Branch');
        foreach ($branches as $branch) {
            
        }
        return array('UDP'=>1,'JHD'=>80);
    } 

    function test_BackDate(){
        return array('UDP'=>1,'JHD'=>80);
    }
}