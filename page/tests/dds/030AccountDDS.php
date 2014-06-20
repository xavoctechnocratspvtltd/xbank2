<?php

class page_tests_dds_030AccountDDS extends Page_Tester {

    public $title = 'DDS Account Testing';
    public $account_type = ACCOUNT_TYPE_DDS;
    public $account;
    public $member;
    public $scheme;
    public $Amount; // DDS AMOUNT 
    public $accounts_that_will_be_checked=array();

    public $proper_responses=array(
        "Test_accountType"=>array('type'=>ACCOUNT_TYPE_DDS,'member'=>'GOWRAV VISHWAKARMA ','scheme'=>'new 3 Month DDS', 'Agent'=>'MEENA DEVRA'),
        'Test_CreateAccount'=>array(),
        'Test_otherAccountsBalance'=>array(),
        'Test_createTimeTransactions'=>array(),
        'Test_accountFlow'=>array(),
        'Test_accountMaturity'=>array('maturity_date'=>'2014-08-07','MaturedStatus'=>1),
    );

    public $account_flow=array(
            'open'=>'2014-02-07',
            'flow'=>array(
                    // NO two transactions on same date .. array key will get replaced
                    '2014-02-10'=> 300,
                    '2014-02-12'=> 300,
                    '2014-04-08'=> array(300,'from_branch_code'=>'JHD'),
                    '2014-05-06'=> 300
                ),
            'test_till'=>'2014-05-07'
        );

    function prepare(){
        $m = $this->member = $this->add('Model_Member');
        $m->load(1035); // Gowrav Vishwakarma

        $s = $this->scheme = $this->add('Model_Scheme');
        // $s->load(81); // DDS 1 YEAR PLAN
        $s->load(208); // new 3 Month DDS

        $a = $this->agent = $this->add('Model_Agent');
        $a->load(11); // Meena Devra

        $this->add('Model_Closing')
            ->addCondition('branch_id',$this->api->current_branch->id)
            ->tryLoadAny()
            ->set('daily',date('Y-m-d',strtotime($this->account_flow['open'].' -1 days')))
            ->update();

        $this->Amount=300;
        $this->proper_responses['Test_accountType'] += array('Amount'=>$this->Amount);

        // Make All Other Used Accounts Balance to ZERO so that easy checking is possible for each scheme
        // OTHERWISE.. proper_responses will be always change on the base of flow of test running

        $this->accounts_that_will_be_checked = array(
                // ACCOUNT_NUMBER => array(array(after_create_account_transaction_DR,CR),array('after_closing_done,DR,CR'))
                $this->api->current_branch['Code'].SP.BRANCH_TDS_ACCOUNT =>array(array(0,0),array(0,0,)),
                'UDRSB373' =>array(array(0,0),array(10,20,)),
            );

        $reset_account = $this->add('Model_Account');
        foreach($this->accounts_that_will_be_checked as $AccountNumber=>$arrays){
            $reset_account->unload();
            $reset_account->loadBy('AccountNumber',$AccountNumber);

            $reset_account['OpeningBalanceDr']=0;
            $reset_account['OpeningBalanceCr']=0;
            $reset_account['CurrentBalanceDr']=0;
            $reset_account['CurrentBalanceCr']=0;

            $reset_account->saveAndUnload();
        }

        $this->add('Model_Transaction')->deleteAll();
        $this->add('Model_TransactionRow')->deleteAll();

        return null;
    }

    function test_accountType(){
        return array('type'=>$this->account_type,'member'=>$this->member['name'],'scheme'=>$this->scheme['name'],'Amount'=>$this->Amount);
    }

    function prepare_CreateAccount(){
        $AccountNumber = 'UDR'.$this->account_type.'X'.rand(1000,9999);
        $this->account = $account = $this->add('Model_Account_'.$this->account_type);
        $account->createNewAccount($this->member->id,$this->scheme->id,$this->api->current_branch, $AccountNumber,$otherValues=array('Amount'=>$this->Amount,'agent_id'=>$this->agent->id),$form=null,$created_at=$this->account_flow['open']);
        $this->api->memorize('new_account_number',$AccountNumber);
        // ++++++++
        $this->proper_responses['Test_CreateAccount'] +=array('AccountNumber'=>$AccountNumber,'member_id'=>$this->member->id, 'maturity_date'=>'2014-08-07','Agent'=>$this->agent->ref('member_id')->get('name'));
    }

    function test_CreateAccount(){
        return array(
            'AccountNumber'=>$this->account['AccountNumber'],
            'member_id'=>$this->account['member_id'],
            'maturity_date'=>$this->account['maturity_date'],
            'Agent'=>$this->account->ref('agent_id')->ref('member_id')->get('name'),
            );
    } 

    function prepare_otherAccountsBalance(){
        if(!$this->account)
            throw $this->exception('Must run complete page tests, individual test not permitted','SkipTests');

        foreach ($this->accounts_that_will_be_checked as $AccountNumber => $arrays) {
            // +++++++++++++
            $this->proper_responses['Test_otherAccountsBalance'] += array(
                    $AccountNumber => $arrays[0] // arrays[0] is array(Dr,CR)
                );
        }
        
    }

    function test_otherAccountsBalance(){
        $result = array();
        foreach ($this->accounts_that_will_be_checked as $AccountNumber => $arrays) {
            $account= $this->add('Model_Account')->loadBy('AccountNumber',$AccountNumber);
            $result += array(
                    $AccountNumber=> array($account['CurrentBalanceDr'],$account['CurrentBalanceCr'])
                );
        }

        return $result;
    }

    function prepare_createTimeTransactions(){
        if(!$this->account)
            throw $this->exception('Must run complete page tests, individual test not permitted','SkipTests');

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

    function prepare_accountFlow(){
    
        if(!$this->account)
            throw $this->exception('Must run complete page tests, individual test not permitted','SkipTests');

        $date = $this->account_flow['open'];
        $test_till = $this->api->nextDate($this->account_flow['test_till']);

        while(strtotime($date) <= strtotime($test_till)){

            if(isset($this->account_flow['flow'][$date])){
                $date_value = $this->account_flow['flow'][$date];
                if(is_array($date_value)){
                    $amount = $date_value[0];
                    $transaction_in_branch = $this->add('Model_Branch')->loadBy('Code',$date_value['from_branch_code']);
                }else{
                    $amount=$date_value;
                    $transaction_in_branch = $this->account->ref('branch_id');
                }

                try{
                    if($amount > 0)
                        $this->account->deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$on_date=$date, $transaction_in_branch);
                    else
                        $this->account->withdrawl(abs($amount),$narration=null,$accounts_to_credit=null,$form=null,$on_date=$date, $transaction_in_branch);
                }catch(Exception $e){
                    $this->add('View_Error')->setHTML('<b>'.$date. '</b> with amount <b>'.$amount.'</b> generated exception <i><u>'. $e->getMessage().'</u></i>');
                }
            }
            $this->account->ref('branch_id')->performClosing($on_date=$date, $test_scheme=$this->scheme, $test_account = $this->account);
            $this->account->reload();
            $date = date('Y-m-d',strtotime($date .' +1 days'));
        }

        foreach ($this->accounts_that_will_be_checked as $AccountNumber => $arrays) {
            $account= $this->add('Model_Account')->loadBy('AccountNumber',$AccountNumber);
            $this->proper_responses['Test_accountFlow'] += array(
                    $AccountNumber => $arrays[1] // account dr,cr after transactions
                );
        }

    }


    function test_accountFlow(){
        $result = array();
        foreach ($this->accounts_that_will_be_checked as $AccountNumber => $arrays) {
            $account= $this->add('Model_Account')->loadBy('AccountNumber',$AccountNumber);
            $result += array(
                    $AccountNumber=> array($account['CurrentBalanceDr'],$account['CurrentBalanceCr'])
                );
        }

        return $result;
    }

    function prepare_accountMaturity(){

    }

    function test_accountMaturity(){
        $account = $this->add('Model_Account_'.$this->account_type)->loadBy('AccountNumber',$this->api->recall('new_account_number'));
        return  array('maturity_date'=>$account['maturity_date'],'MaturedStatus'=>$account['MaturedStatus']);
    }
}