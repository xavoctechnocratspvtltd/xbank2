<?php

class page_tests_011BranchFunctions extends Page_Tester {
    public $title = 'Branch Function Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_getDefaultMember'=>array(),
        'Test_newVoucherNumberToday'=>array()
    );

    function prepare(){
        return null;
    }

    function test_getDefaultMember(){
        $result=array();
        foreach ($b=$this->add('Model_Branch') as $b_array) {
            $this->proper_responses['Test_getDefaultMember'] += array($b_array['Code'] => $b_array['Code'].' Default');
            $result += array($b_array['Code'] => $b->getDefaultMember()->get('name'));
        }
        return $result;
    }

    function prepare_newVoucherNumberToday(){
        $this->proper_responses['Test_newVoucherNumberToday']=array(
                'DFL'=>1,
                'UDR'=>3395,
                'JHD'=>3210,
                'OGN'=>1652,
                'TUD'=>1,
                'GOG'=>2758,
                'SYR'=>203

            );
    }

    function test_newVoucherNumberToday(){
        $result = array();
        $f_y= $this->api->getFinancialYear();
        $b=$this->add('Model_Branch');
        
        foreach ($b as $b_array) {
            $result += array($b_array['Code']=>$b->newVoucherNumber($b->id));
        }
        return $result;
    }

    function prepare_newVoucherNumberPastYear(){
        $this->proper_responses['Test_newVoucherNumberPastYear']=array(
                'ON_DATE'=>'2014-03-31',
                'DFL'=>1,
                'UDR'=>58492,
                'JHD'=>25737,
                'OGN'=>16839,
                'TUD'=>1,
                'GOG'=>24325,
                'SYR'=>2385

            );
    }

    function test_newVoucherNumberPastYear(){
        $f_y= $this->api->getFinancialYear('2014-01-01');
        $b=$this->add('Model_Branch');

        $result = array('ON_DATE'=>$f_y['end_date']);
        
        foreach ($b as $b_array) {
            $result += array($b_array['Code']=>$b->newVoucherNumber($b->id,$f_y['end_date']));
        }
        return $result;
    }

    function prepare_newVoucherNumberNextYear(){
        $this->proper_responses['Test_newVoucherNumberNextYear']=array(
                'ON_DATE'=>'2016-03-31',
                'DFL'=>1,
                'UDR'=>1,
                'JHD'=>1,
                'OGN'=>1,
                'TUD'=>1,
                'GOG'=>1,
                'SYR'=>1

            );
    }

    function test_newVoucherNumberNextYear(){
        $f_y= $this->api->getFinancialYear('2015-12-01');
        $b=$this->add('Model_Branch');
        
        $result = array('ON_DATE'=>$f_y['end_date']);
        foreach ($b as $b_array) {
            $result += array($b_array['Code']=>$b->newVoucherNumber($b->id,$f_y['end_date']));
        }
        return $result;
    }

    function prepare_newVoucherNumberMidDay(){
        $this->proper_responses['Test_newVoucherNumberMidDay']=array(
                'ON_DATE'=>'2014-04-10',
                'DFL'=>1,
                'UDR'=>1,
                'JHD'=>1,
                'OGN'=>1,
                'TUD'=>1,
                'GOG'=>1,
                'SYR'=>1

            );
    }

    function test_newVoucherNumberMidDay(){
        $b=$this->add('Model_Branch');
        $on_date = '2014-04-10';
        $result = array('ON_DATE'=>$on_date);
        foreach ($b as $b_array) {
            $result += array($b_array['Code']=>$b->newVoucherNumber($b->id,$on_date));
        }
        return $result;
    }

    function prepare_newVoucherNumberMidDayPastYear(){
        $this->proper_responses['Test_newVoucherNumberMidDayPastYear']=array(
                'ON_DATE'=>'2013-04-10',
                'DFL'=>1,
                'UDR'=>1,
                'JHD'=>1,
                'OGN'=>1,
                'TUD'=>1,
                'GOG'=>1,
                'SYR'=>1

            );
    }

    function test_newVoucherNumberMidDayPastYear(){
        $b=$this->add('Model_Branch');
        $on_date = '2013-04-10';
        $result = array('ON_DATE'=>$on_date);
        foreach ($b as $b_array) {
            $result += array($b_array['Code']=>$b->newVoucherNumber($b->id,$on_date));
        }
        return $result;
    }
}