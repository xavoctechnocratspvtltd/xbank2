<?php

class page_tests_01branch extends Page_Tester {
    public $title = 'Branch Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        'Test_branchCreate'=>array('staff_count'=>1,'new_staff_accessLevel'=>80),
        'Test_branchDelete'=>array('branch_loaded'=>"0",'staff_count'=>0,'accounts_count'=>0,'members_count'=>0)
    );

    function prepare(){
        return null;
    }

    function prepare_branchCreate(){
        $branch = $this->add('Model_Branch');
        $branch['name']='x'.rand(1000,9999);
        $branch['Code']='x'.rand(1000,9999);

        $this->proper_responses['Test_branchCreate'] += array('default_member_name'=>substr($branch['Code'],0,3). ' Default');

        $branch->save();
        echo $branch->id;
        $this->api->memorize('newbranch_id',$branch->id);
        return array($branch->id);
    }

    function test_branchCreate($bid){
        $newbranch = $this->add('Model_Branch')->load($bid);
        $newstaff=$newbranch->ref('Staff')->tryLoadAny();

        $member=$this->add('Model_Member')->addCondition('branch_id',$newbranch->id)->tryLoadAny()->get('name');

        return array(
            'staff_count'=>$newstaff->count()->getOne(), 
            'new_staff_accessLevel'=>$newstaff['AccessLevel'],
            'default_member_name'=>$member
            );
    }

    function test_branchDelete(){
        $newbranch = $this->add('Model_Branch')->tryLoad($this->api->recall('newbranch_id'));
        $newbranch->delete();

        $bid= $this->api->recall('newbranch_id');

        $staff= $this->add('Model_Staff');
        $staff->addCondition('branch_id',$bid);

        $accounts = $this->add('Model_Account')->addCondition('branch_id',$bid)->count()->getOne();
        $member = $this->add('Model_Member')->addCondition('branch_id',$bid)->count()->getOne();

        return array(
            'branch_loaded'=>$newbranch->loaded()?:0,
            'staff_count'=>$staff->count()->getOne(),
            'accounts_count' => $accounts,
            'members_count' => $member,
            );

    }   
}