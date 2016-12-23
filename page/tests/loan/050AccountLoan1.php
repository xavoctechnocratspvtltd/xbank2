<?php

class page_tests_loan_050AccountLoan1 extends Page_Tester {
    // Class Variables
    public $title = 'Account Testing';
    public $account_type = 'Loan'; //Loan,CC,FixedAndMis,Default,SavingAndCurrent,Recurring,DDS
    public $AccountNumber=null;
    public $account=null;
    public $member=null;
    public $scheme=null;
    public $agent=null;

    // ============= input starts

    public $member_id=1035; // Gowrav Vishwakarama
    public $agent_id=11; // Meena Devra
    public $scheme_id=169; // NEW VL 18 MONTHS 
    public $dealer_id=32; // BMJ CHAMAN SINGH
    // public $dealer_id=0; // BMJ CHAMAN SINGH

    public $cheque_issued=array(
            array(101,150),
            array(801,850),
        );

    public $maturityToAccount = null; // Maturity to transfer to account "UDRSBxyz"
    public $interestToAccount = null; // Interest To Account "UDRSBabcd"
    public $loan_from_account='1845'; // SBBJ (UDR) 61097150947
    public $account_type_in_account = 'Two Wheeler Loan'; // Account Type

    // Amount is what ???
    // Saving => Initial Opening Amount
    // CC => CC Limit
    // DDS => DDS amount 
    // Default => Initial Opening Amount
    // FixedAndMis => FD/MIS Amount 
    // Loan => Loan Amount 
    // Recurring => RECURRING amount (premium) 

    public $Amount = 30000 ;

    // Account Flow
    // 'date'  => array(amount,'cheque_no'=>cheque_no,'from_branch_code'=>CODE)
    public $account_flow=array(
            'open'=>'2014-01-30',
            'flow'=>array(
                    // NO two transactions on same date .. array key will get replaced
                    '2014-02-10'=> array(2201),
                    '2014-04-28'=> array(4402,'cheque_no'=>132514),
                    '2014-05-30'=> array(2201,'from_branch_code'=>'JHD'),
                    '2014-06-06'=> array(2201),
                    '2014-07-06'=> array(2201),
                    '2014-09-15'=> array(4402),
                    '2014-12-15'=> array(4402),
                    '2015-01-11'=> array(4402),
                    '2015-02-11'=> array(4402),
                    '2015-04-10'=> array(2201),
                    '2015-05-10'=> array(2201),
                    '2015-09-15'=> array(4402),

                ),
            'test_till'=>'2015-09-16',
            // 'test_till'=>'2014-03-11'
        );
    
    // Proper_Responses
    public $proper_responses=array(
        "Test_beforeAccountCreate"=>array(
                    'accounts_to_check'=>array(
                            'new_account'=>array( 0,0), /* DR, CR */
                            // 'SBBJ (UDR) 61097150947'=>array( 0,0), /* DR, CR */
                            // 'UDR Processing Fee Received On NEW VL 18 MONTHS '=>array( 0,0), /* DR, CR */
                            // 'UDR Penalty Due To Late Payment On NEW VL 18 MONTHS '=>array(0,0), /* DR, CR */
                        ),
                ),
        'Test_afterAccountCreate'=>array(
                    'member'=>'GOWRAV VISHWAKARMA ',
                    'scheme'=>'NEW VL 18 MONTHS ',
                    'type'=>'Loan', // Loan,CC,FixedAndMis,Default,SavingAndCurrent,Recurring,DDS
                    'Agent'=>null,
                    'maturity_date'=>'2014-08-07',
                    'accounts_to_check'=>array(
                            'new_account'=>array( 30000,0), /* DR, CR */
                            'SBBJ (UDR) 61097150947'=>array( 0,29250), /* DR, CR */
                            'UDR Processing Fee Received On NEW VL 18 MONTHS '=>array( 0,750), /* DR, CR */
                            'UDR Penalty Due To Late Payment On NEW VL 18 MONTHS '=>array(0,0), /* DR, CR */
                            'UDR Interest Received On NEW VL 18 MONTHS '=>array(0,0), /* DR, CR */
                        ),
                ),
        'Test_afterAllTransactions'=>array(
                    'accounts_to_check'=>array(
                            'new_account'=>array( 38074,0), /* DR, CR */
                            'SBBJ (UDR) 61097150947'=>array( 0,29250), /* DR, CR */
                            'UDR Processing Fee Received On NEW VL 18 MONTHS '=>array( 0,750), /* DR, CR */
                            'UDR Penalty Due To Late Payment On NEW VL 18 MONTHS '=>array(0,645), /* DR, CR */
                            'UDR Interest Received On NEW VL 18 MONTHS '=>array(0,7429), /* DR, CR */
                        )
            ),
        'Test_accountMaturity'=>array(
                    'MaturedStatus'=>1,
                    'accounts_to_check'=>array(
                            'new_account'=> array( 0,0), /* DR, CR */

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
        $account->createNewAccount($this->member->id,$this->scheme->id,$this->api->current_branch, $this->AccountNumber,$otherValues=array('Amount'=>$this->Amount,'agent_id'=>$this->agent->id,'account_type'=>$this->account_type_in_account,'loan_from_account'=>$this->loan_from_account,'dealer_id'=>$this->dealer_id, 'extra_info'=>json_encode(array())),$form=null,$created_at=$this->account_flow['open']);
        $this->proper_responses['Test_afterAccountCreate']['AccountNumber'] = $this->AccountNumber;
    }

    function test_afterAccountCreate(){
        $result = array(
                    'member'=>$this->account->ref('member_id')->get('name'),
                    'scheme'=>$this->account->ref('scheme_id')->get('name')
                );

        $result['type'] = $this->account->ref('scheme_id')->get('SchemeType');
        
        if($this->account->hasElement('agent_id'))
            $result['Agent'] = $this->account->ref('agent_id')->get('name');
        else
            $result['Agent'] = null;

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