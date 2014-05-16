<?php

class page_tests_02SchemeCC extends Page_Tester {
    public $title = 'Schemes Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_schemeCreateCC_1'=>array('udr_default_accounts_count'=>1,'jhd_default_accounts_count'=>80),
        'Test_schemeDeleteCC_1'=>array('udr_default_accounts'=>0,'jhd_default_accounts_count'=>0)
        // 'Test_branchDefaultAccounts'=>20,
    );

    function prepare(){
        return null;
    }

    function test_Test_schemeCreateCC_1(){
        $scheme = $this->add('Model_Scheme_CC');
        array('Interest','AccountOpenningCommission','ActiveStatus','balance_sheet_id','ProcessingFeesinPercent','ProcessingFees','SchemePoints','SchemeGroup');
        $scheme['name']='';
        $scheme['MinLimit']='0';
        $scheme['MaxLimit']='-1';
        $scheme['Interest']='18';
        $scheme['AccountOpenningCommission']=0;
        $scheme['balance_sheet_id']=2;
        $scheme['ProcessingFeesinPercent']=true;
        $scheme['ProcessingFees']=2;
        $scheme['SchemeGroup']='CC';
        $scheme->save();
    }

     
}