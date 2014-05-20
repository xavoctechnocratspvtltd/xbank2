<?php

class page_tests_015MemberFunctions extends Page_Tester {
    public $title = 'Member Function Testing';

    public $proper_responses=array(
        "Test_empty"=>'',
        "Test_createNewMember"=>array('saved'=>1,'sm_account_number'=>'SM8196'),
        "Test_deleteMember"=>array('removed'=>1,'sm_account_number'=>0),
    );

    function prepare(){
        return null;
    }

    function prepare_createNewMember(){
    	$m=$this->add('Model_Member');
    	$m->createNewMember('test_user_name', $admissionFee=10, $shareValue=100,null,array('Occupation'=>'Service'));

        // +++++++++
        $this->proper_responses['Test_createNewMember'] += array('sm_account_member_id'=>$m->id);

    	$this->api->memorize('test_member_id',$m->id);
    }

    function test_createNewMember(){
    	$m_c= $this->add('Model_Member');
    	$m_c->tryLoadBy('name','test_user_name');

        $sm_account = $this->add('Model_Account');
        $sm_account->addCondition('scheme_name',CAPITAL_ACCOUNT_SCHEME);
        $sm_account->setOrder('id','desc');
        $sm_account->tryLoadAny();

    	return array('saved'=>$m_c->loaded(),'sm_account_number'=>$sm_account['AccountNumber'],'sm_account_member_id'=>$sm_account['member_id']);
    }



    function prepare_deleteMember(){
        $m=$this->add('Model_Member');
        $m->tryLoad($m_id=$this->api->recall('test_member_id',0));
        $m->delete(true);
    }

    function test_deleteMember(){
        $m=$this->add('Model_Member');
        $m->tryLoad($m_id=$this->api->recall('test_member_id',0));


        $a=$this->add('Model_Account');
        $a->addCondition('member_id',$m_id);

        return array('removed'=>($m->loaded()?0:1), 'accounts'=>$a->count()->getOne());
    }
}