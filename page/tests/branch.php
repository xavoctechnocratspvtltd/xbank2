<?php

class page_tests_branch extends Page_Tester {
    public $title = 'Branch Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_branchCreate'=>array('staff_count'=>1,'new_staff_accessLevel'=>80),
        // 'Test_branchDefaultAccounts'=>20,
        'Test_branchDelete'=>array('branch_loaded'=>"0",'staff_count'=>0,'default_accounts'=>array('a'=>40,'b'=>20))
    );

    function prepare(){
        return null;
    }

    function prepare_branchCreate(){
        $branch = $this->add('Model_Branch');
        $branch['name']='test'.rand(1000,9999);
        $branch->save();
        $this->api->memorize('newbranch_id',$branch->id);
        return array($branch->id);
    }

    function test_branchCreate($bid){
        echo $bid;
        $newbranch = $this->add('Model_Branch')->load($bid);
        $newstaff=$newbranch->ref('Staff')->tryLoadAny();
        return array('staff_count'=>$newstaff->count()->getOne(), 'new_staff_accessLevel'=>$newstaff['AccessLevel']);
    }

    function test_branchDelete(){
        $newbranch = $this->add('Model_Branch')->tryLoad($this->api->recall('newbranch_id'));
        $newbranch->delete();

        $staff= $this->add('Model_Staff');
        $staff->addCondition('branch_id',$this->api->recall('newbranch_id'));

        return array('branch_loaded'=>$newbranch->loaded()?:0,'staff_count'=>$staff->count()->getOne());

    }   
}