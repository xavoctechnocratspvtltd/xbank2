<?php

class page_tests_sbca_040AccountSB1 extends Page_Tester {
    // Class Variables
    public $title = 'Account Testing';
    public $account_type = 'SavingAndCurrent'; //Loan,CC,FixedAndMis,Default,SavingAndCurrent,Recurring,DDS
    public $AccountNumber=null;
    public $account=null;
    public $member=null;
    public $scheme=null;
    public $agent=null;

    // ============= input starts

    public $member_id=1035; // Gowrav Vishwakarama *
    public $agent_id=11; // Meena Devra *
    public $scheme_id = 1;
    public $cheque_issued=array(
            array(101,150),
            array(801,850),
        );

    public $maturityToAccount = null; // Maturity to transfer to account "UDRSB1162"
    public $interestToAccount = null; // Interest To Account "UDRSB1162"

    // Amount is what 0
    // Saving => Saving Account
    // CC => CC Limit
    // DDS => DDS amount 
    // Default => Initial Opening Amount
    // FixedAndMis => FD
    // Loan => Loan Amount 
    // Recurring => RECURRING amount (premium) 

    public $Amount = 0 ;

    // Account Flow
    // 'date'  => array(amount,'cheque_no'=>cheque_no,'from_branch_code'=>CODE)
    public $account_flow=array(
            'open'=>'2013-10-01',
            'flow'=>array(
                    // NO two transactions on same date .. array key will get replaced
                    '2014-02-10'=> array(100),
                    '2014-02-12'=> array(500,'cheque_no'=>132514),
                    '2014-04-08'=> array(-400,'from_branch_code'=>'JHD'),
                    '2014-04-09'=> array(-100,'from_branch_code'=>'SYR'),
                    '2014-05-06'=> array(1000),
                    '2014-06-06'=> array(500),
                ),
            'test_till'=>'2014-07-01',
        );
    
    // Proper_Responses
    public $proper_responses=array(
        "Test_beforeAccountCreate"=>array(
                    'accounts_to_check'=>array(
                            'new_account'=>array( 0,0), /* DR, CR */
                    'UDR Interest Paid On Saving Account'    => array(0,0)
                        ),
                ),
        'Test_afterAccountCreate'=>array(
                    'member'=>'GOWRAV VISHWAKARMA ',
                    'scheme'=>'Saving Account',
                    'type'=>'SavingAndCurrent',
                    'Agent'=>'MEENA DEVRA',
                    'maturity_date'=>'',
                    'accounts_to_check'=>array(
                            'new_account'=>array( 0,0), /* DR, CR */
                            'UDR Interest Paid On Saving Account' =>array(0,0)
                        ),
                ),
        'Test_afterAllTransactions'=>array(
                    'accounts_to_check'=>array(
                            'new_account'=> array( 00,1600), /* DR, CR */
                            'UDRSB1162' => array(0,0),
                           'UDR Interest Paid On Saving Account'=>array(0,32)
                        )
            ),
        'Test_accountMaturity'=>array(
                    'MaturedStatus'=>1,
                    'accounts_to_check'=>array(
                            'new_account'=> array( 0,0), /* DR, 1632 */
                            'UDR Interest Paid On Saving Account'=>array(0,1632)

                        ),
                    ),
    );

    
    // ============ INPUT ENDS =================

    function prepare_beforeAccountCreate(){
        $this->member = $this->add('Model_Member')->load($this->member_id);
        $this->scheme = $this->add('Model_Scheme')->load($this->scheme_id);
        $this->agent = $this->add('Model_Agent')->load($this->agent_id);
        $this->AccountNumber = 'UDR'.$this->account_type.'X'.rand(1000,9999);

         // Reset Closing
         $this->add('Model_Closing')
            ->addCondition('branch_id',$this->api->current_branch->id)
            ->tryLoadAny()
            ->set('daily',date('Y-m-d',strtotime($this->account_flow['open'].' -1 days')))
            ->update();
        
        // Make all used accounts to zero for easy checking
        $reset_account = $this->add('Model_Account');

        foreach ($this->proper_responses as $test => $values) {
            if(!isset($values['accounts_to_check'])) continue;

            foreach ($values['accounts_to_check'] as $AccountNumber => $values) {
                if($AccountNumber == 'new_account') continue;

                $reset_account->unload();
                $reset_account->loadBy('AccountNumber',$AccountNumber);

                $reset_account['OpeningBalanceDr']=0;
                $reset_account['OpeningBalanceCr']=0;
                $reset_account['CurrentBalanceDr']=0;
                $reset_account['CurrentBalanceCr']=0;

                $reset_account->saveAndUnload();
            }
        }

        $this->add('Model_Transaction')->deleteAll();
        $this->add('Model_TransactionRow')->deleteAll();

    }

    function Test_beforeAccountCreate(){
        return array(
                'accounts_to_check'=>array(
                        'new_account'=>array(0,0),
                    )
            );
    }

    function prepare_afterAccountCreate(){
        $this->account = $account = $this->add('Model_Account_'.$this->account_type,array('allow_any_name'=>true));
        $account->createNewAccount($this->member->id,$this->scheme->id,$this->api->current_branch, $this->AccountNumber,$otherValues=array('Amount'=>$this->Amount,'agent_id'=>$this->agent->id),$form=null,$created_at=$this->account_flow['open']);
        $this->proper_responses['Test_afterAccountCreate']['AccountNumber'] = $this->AccountNumber;
    }

    function test_afterAccountCreate(){
        $result = array(
                    'member'=>$this->account->ref('member_id')->get('name'),
                    'scheme'=>$this->account->ref('scheme_id')->get('name')
                );

        $result['type'] = $this->account->ref('scheme_id')->get('SchemeType');
        $result['Agent'] = $this->account->ref('agent_id')->get('name');
        $result['maturity_date'] = $this->account['maturity_date'];

        $result['accounts_to_check'] = $this->account_to_check_balances('Test_afterAccountCreate');
        $result['AccountNumber'] = $this->account['AccountNumber'];

        return $result;
    }

    function prepare_afterAllTransactions(){
        if(!$this->account)
            throw $this->exception('Must run complete page tests, individual test not permitted','SkipTests');

        $date = $this->account_flow['open'];
        $test_till = $this->api->nextDate($this->account_flow['test_till']);

        while(strtotime($date) <= strtotime($test_till)){

            if(isset($this->account_flow['flow'][$date])){
                $date_value = $this->account_flow['flow'][$date];
                $amount = $date_value[0];
                $transaction_in_branch = $this->account->ref('branch_id');
                if(isset($date_value['from_branch_code'])){
                    $transaction_in_branch = $this->add('Model_Branch')->loadBy('Code',$date_value['from_branch_code']);
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
    }

    function test_afterAllTransactions(){
        $result = array();
        $result['accounts_to_check'] = $this->account_to_check_balances('Test_afterAllTransactions');
        return $result;
    }

    function test_accountMaturity(){
        $result= array();
        $result['MaturedStatus'] = $this->account['MaturedStatus'];
        $result['accounts_to_check'] = $this->account_to_check_balances('Test_accountMaturity');
        return $result;
    }

    function account_to_check_balances($test){
        $balances = array();
        $account_check = $this->add('Model_Account');

        foreach ($this->proper_responses[$test]['accounts_to_check'] as $AccountNumber => $requiredValues) {
            $loadAccountNumber=$AccountNumber;
            if($AccountNumber == 'new_account') $loadAccountNumber = $this->AccountNumber;

            $account_check->loadBy('AccountNumber',$loadAccountNumber);
            $balances[$AccountNumber] = array($account_check['CurrentBalanceDr'],$account_check['CurrentBalanceCr']);
            $account_check->unload();
        }

        return $balances;
    }

}