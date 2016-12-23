<?php

class page_tests_020SchemeDDS extends Page_Tester {
    public $title = 'Schemes Testing';
    public $scheme_type = ACCOUNT_TYPE_DDS;

    public $proper_responses=array(
        "Test_schemeName"=>ACCOUNT_TYPE_DDS,
        'Test_schemeCreate_1'=>array('udr_default_accounts_count'=>2,'jhd_default_accounts_count'=>2),
        'Test_schemeDelete_1'=>array('udr_default_accounts'=>0,'jhd_default_accounts_count'=>0)
        // 'Test_branchDefaultAccounts'=>20,
    );

    function prepare(){
        return null;
    }

    function test_schemeName(){
        return $this->scheme_type;
    }

    function prepare_schemeCreate_1(){
        $scheme = $this->add('Model_Scheme_'.$this->scheme_type);
        $scheme->createNewScheme('TEST '. $this->scheme_type.' '.rand(1000,9999),2, $this->scheme_type,$this->scheme_type, $loanType_if_loan = $this->scheme_type, $other_values=array(),$form=null,$on_date=null);
        $this->api->memorize('new_scheme_id',$scheme->id);
        return array($scheme->id);
    }

    function test_schemeCreate_1($scheme_id){
        $new_scheme = $this->add('Model_Scheme');
        $new_scheme->load($scheme_id);

        $udr_accounts = $this->add('Model_Account');
        $udr_accounts->addCondition('AccountNumber','like',"%".$new_scheme['name']."%");
        $udr_accounts->addCondition('branch_id',2);

        $jhd_accounts = $this->add('Model_Account');
        $jhd_accounts->addCondition('AccountNumber','like',"%".$new_scheme['name']."%");
        $jhd_accounts->addCondition('branch_id',3);

        return array(
                'udr_default_accounts_count' =>$udr_accounts->count()->getOne(),
                'jhd_default_accounts_count' =>$jhd_accounts->count()->getOne()
            );
    }

    function test_schemeDelete_1(){
        $scheme = $this->add('Model_Scheme_'.$this->scheme_type);
        $scheme->load($this->api->recall('new_scheme_id',0));
        
        $new_scheme_name = $scheme['name'];

        $scheme->prepareDelete();
        $scheme->delete();

        $udr_accounts = $this->add('Model_Account');
        $udr_accounts->addCondition('AccountNumber','like',"UDR%".$new_scheme_name."%");
        $udr_accounts->addCondition('branch_id',2);

        $jhd_accounts = $this->add('Model_Account');
        $jhd_accounts->addCondition('AccountNumber','like',"JHD%".$new_scheme_name."%");
        $jhd_accounts->addCondition('branch_id',3);

        return array(
                'udr_default_accounts_count' =>$udr_accounts->count()->getOne(),
                'jhd_default_accounts_count' =>$jhd_accounts->count()->getOne()
            );
    }
}