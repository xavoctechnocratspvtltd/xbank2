<?php

class page_tests_02scheme extends Page_Tester {
    public $title = 'Schemes Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_schemeCreateLoan_1'=>array('staff_count'=>1,'new_staff_accessLevel'=>80),
        'Test_schemeDelete'=>array('branch_loaded'=>"0",'staff_count'=>0,'default_accounts'=>array('a'=>40,'b'=>20))

        // 'Test_branchDefaultAccounts'=>20,
    );

    function prepare(){
        return null;
    }

     
}