<?php

class page_tests_voucherno extends Page_Tester {
    public $title = 'Voucher Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_Next'=>array('UDP'=>1,'JHD'=>80),
        'Test_BackDate'=>array('UDP'=>1,'JHD'=>80),
        'Test_BackDate2'=>array('UDP'=>1,'JHD'=>80),
        'Test_BackDate3'=>array('DFL'=>'1','UDR'=>'105.10','JHD'=>'220.10','OGN'=>'111.10','TUD'=>'1','GOG'=>'253.10','SYR'=>'82'),
    );

    function prepare(){
        $branches = $this->add('Model_Branch');
        foreach ($branches as $branch) {
            $next_voucher_no = 'next_voucher_no_'. $branch->id;
            unset($this->api->$next_voucher_no);
        }
        return null;
    }

    // function prepare_Next(){
    //     return array($branch->id);
    // }

    function test_Next(){
        $result = array();
        $branches = $this->add('Model_Branch');
        foreach ($branches as $branch) {
            $result[$branch['Code']] = $branch->newVoucherNumber($branch);
        }
        return $result;
    } 

    function test_BackDate(){
        $result = array();
        $branches = $this->add('Model_Branch');
        foreach ($branches as $branch) {
            $result[$branch['Code']] = $branch->newVoucherNumber($branch,'2015-04-07');
        }
        return $result;
    }

    function test_BackDate2(){
        $result = array();
        $branches = $this->add('Model_Branch');
        for($i=1;$i<=10;$i++){
            foreach ($branches as $branch) {
                $result[$branch['Code']] = (string) $branch->newVoucherNumber($branch,'2015-04-07');
            }
        }
        return $result;
    }

    function test_BackDate3(){
        $result = array();
        $branches = $this->add('Model_Branch');
        foreach ($branches as $branch) {
            $result[$branch['Code']] = (string) $branch->newVoucherNumber($branch,'2015-04-07');
        }
        return $result;
    }
}