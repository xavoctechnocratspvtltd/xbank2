<?php

class page_tests_010Branch extends Page_Tester {
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
        $this->proper_responses['Test_branchCreate'] += array('new_staff_username'=>$this->api->normalizeName($branch['name'].' admin'));

        $branch->save();

        $other_branches = $this->add('Model_Branch')->addCondition('id','<>',$branch->id);

        $this->proper_responses['Test_branchCreate'] += array('branch_and_division_for'=>$other_branches->count()->getOne());
        
        $this->api->memorize('newbranch_id',$branch->id);
        return array($branch->id);
    }

    function test_branchCreate($bid){
        $newbranch = $this->add('Model_Branch')->load($bid);
        $newstaff=$newbranch->ref('Staff')->tryLoadAny();

        $member=$this->add('Model_Member')->addCondition('branch_id',$newbranch->id)->tryLoadAny()->get('name');

        $branch_and_devision_accounts= $this->add('Model_Account');
        $branch_and_devision_accounts->addCondition('AccountNumber','like',"%".SP.BRANCH_AND_DIVISIONS .SP.'for'.SP.$newbranch['Code']);
        $branch_and_devision_accounts_count = $branch_and_devision_accounts->count()->getOne();

        return array(
            'staff_count'=>$newstaff->count()->getOne(), 
            'new_staff_accessLevel'=>$newstaff['AccessLevel'],
            'default_member_name'=>$member,
            'new_staff_username'=>$newstaff['username'],
            'branch_and_division_for'=>$branch_and_devision_accounts_count
            );
    }

    function prepare_branchDelete(){
        $newbranch = $this->add('Model_Branch')->tryLoad($this->api->recall('newbranch_id'));
        if(!$newbranch->loaded())
            throw $this->exception('Branch Must be Created by branchCreate test only.. run the complete test page not perticular test');
    }

    function test_branchDelete(){
        $newbranch = $this->add('Model_Branch')->load($this->api->recall('newbranch_id'));
        $newbranch->delete(true);

        $bid= $this->api->recall('newbranch_id');

        $staff= $this->add('Model_Staff')->addCondition('branch_id',$bid);
        $accounts = $this->add('Model_Account')->addCondition('branch_id',$bid);
        $member = $this->add('Model_Member')->addCondition('branch_id',$bid);

        return array(
            'branch_loaded'=>$newbranch->loaded()?:0,
            'staff_count'=>$staff->count()->getOne(),
            'accounts_count' => $accounts->count()->getOne(),
            'members_count' => $member->count()->getOne(),
            );

    }   
}