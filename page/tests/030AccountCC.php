<?php

class page_tests_030AccountCC extends Page_Tester {
    public $title = 'Account Testing';
    public $account_type = ACCOUNT_TYPE_CC;
    public $account;
    public $member;
    public $scheme;
    public $Amount; // CC Limit in CC Account

    public $proper_responses=array(
        "Test_accountType"=>array('type'=>ACCOUNT_TYPE_CC,'member'=>'GOWRAV VISHWAKARMA ','scheme'=>'C C 18% FILE CHARGE 2.5%'),
        'Test_CreateAccount'=>array(),
        'Test_otherAccountsBalance'=>array(),
        'Test_createTimeTransactions'=>array(),
        'Test_accountFlow'=>array(),
    );

    public $account_flow=array(
            'open'=>'2014-04-07',
            'flow'=>array(
                    '7-4-2014'=>500,
                    '20-4-2014'=>-4000
                )
        );

    function prepare(){
        $m = $this->member = $this->add('Model_Member');
        $m->load(1035); // Gowrav Vishwakarma

        $s = $this->scheme = $this->add('Model_Scheme');
        $s->load(184); // C C 18% FILE CHARGE 2.5%

        return null;
    }

    function test_accountType(){
        return array('type'=>$this->account_type,'member'=>$this->member['name'],'scheme'=>$this->scheme['name']);
    }

    function prepare_CreateAccount(){
        $AccountNumber = 'UDR'.$this->account_type.'X'.rand(1000,9999);
        $this->account = $account = $this->add('Model_Account_'.$this->account_type);
        $this->Amount =30000;
        $account->createNewAccount($this->member->id,$this->scheme->id,$this->api->current_branch, $AccountNumber,$otherValues=array('Amount'=>$this->Amount),$form=null,$created_at=$this->account_flow['open']);
        $this->api->memorize('new_account_number',$AccountNumber);
        // ++++++++
        $this->proper_responses['Test_CreateAccount'] +=array('id'=>27651,'AccountNumber'=>$AccountNumber,'member_id'=>$this->member->id);
    }

    function test_CreateAccount(){
        return array(
            'id'=>$this->account->id,
            'AccountNumber'=>$this->account['AccountNumber'],
            'member_id'=>$this->account['member_id']
            );
    } 

    function prepare_otherAccountsBalance(){
        // +++++++++++++
        $this->proper_responses['Test_otherAccountsBalance'] += array(
                $this->api->current_branch['Code'] . SP . PROCESSING_FEE_RECEIVED . $this->scheme['name'] => (25000+4600),
            );
        
    }

    function test_otherAccountsBalance(){
        return array(
                $this->api->current_branch['Code'] . SP . PROCESSING_FEE_RECEIVED . $this->scheme['name'] => $this->add('Model_Account')->loadBy('AccountNumber',$this->api->current_branch['Code'] . SP . PROCESSING_FEE_RECEIVED . $this->scheme['name'])->get('CurrentBalanceCr')
            );
    }

    function prepare_createTimeTransactions(){
        // ++++++++++++
        $this->proper_responses['Test_createTimeTransactions'] += array(
                'total_transactions'=>1,
                'accounts_engaged_in'=>2
            );
    }

    function test_createTimeTransactions(){
        $transactions =$this->add('Model_Transaction');
        $transactions->join('transaction_row.transaction_id')
                    ->addField('account_id');
        $transactions->addCondition('account_id',$this->account->id);

        $transaction_rows = $this->add('Model_transactionRow');
        $transaction_rows->addCondition('transaction_id',$transactions->tryLoadAny()->id);


        return array(
                'total_transactions'=>$transactions->count()->getOne(),
                'accounts_engaged_in'=>$transaction_rows->count()->getOne()
            );
    }

    function test_accountFlow(){
        
    }
}