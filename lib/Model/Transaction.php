<?php
class Model_Transaction extends Model_Table {
	var $table= "transactions";
	
	public $dr_accounts=array();
	public $cr_accounts=array();


	public $only_transaction=false;
	public $create_called=false;

	public $all_debit_accounts_are_mine = true;
	public $all_credit_accounts_are_mine = true;

	public $other_branch=null;
	public $other_branches_involved = array();

	public $executed=false;

	function init(){
		parent::init();

		$this->hasOne('TransactionType','transaction_type_id');
		$this->hasOne('Staff','staff_id');
		$this->hasOne('Account','reference_id');
		$this->hasOne('Branch','branch_id');
		$this->addField('voucher_no_original')->type('int'); //TODO bigint
		$this->addField('voucher_no')->type('int'); //TODO bigint -- Actual Display Voucher
		$this->addField('Narration')->type('text');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);

		$this->hasMany('TransactionRow','transaction_id');
		
		$this->addHook('beforeSave',$this);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		$this['staff_id'] = $this->api->auth->model->id;
		$this['updated_at'] = $this->api->now;
	}

	function createNewTransaction($transaction_type, $branch=null, $transaction_date=null, $Narration=null, $only_transaction=null,$options=array()){
		if($this->loaded()) throw $this->exception('Use Unloaded Transaction model to create new Transaction');
		
		$transaction_type_model = $this->add('Model_TransactionType');
		$transaction_type_model->tryLoadBy('name',$transaction_type);
		
		if(!$transaction_type_model->loaded()) $transaction_type_model->save();

		if(!$branch) $branch = $this->api->current_branch;

		if(!$transaction_date) $transaction_date = $this->api->now;

		// Transaction TYpe Save if not available
		$this['transaction_type_id'] = $transaction_type_model->id;
		$this['reference_id'] = isset($options['reference_id'])?:0;
		$this['branch_id'] = $branch->id;
		$this['voucher_no'] = $branch->newVoucherNumber($branch,$transaction_date);
		$this['Narration'] = $Narration;
		$this['created_at'] = $transaction_date;

		$this->transaction_type = $transaction_type;
		$this->branch = $branch;
		$this->only_transaction = $only_transaction;
		$this->transaction_date = $transaction_date;
		$this->Narration = $Narration;
		$this->only_transaction = $only_transaction;
		$this->options = $options;

		$this->create_called=true;
	}

	function addDebitAccount($account, $amount){
		if(is_string($account)){
			$account = $this->add('Model_Account')->loadBy('AccountNumber',$account);
		}

		$amount = round($amount,2);
		
		if($account['branch_id'] != $this['branch_id']){
			$this->all_debit_accounts_are_mine = false;
			$this->other_branches_involved[$account['branch_id']] = $account->ref('branch_id');
		}

		$this->dr_accounts += array($account['AccountNumber']=>array('amount'=>$amount,'account'=>$account));
	}

	function addCreditAccount($account, $amount){
		if(is_string($account)){
			$account = $this->add('Model_Account')->loadBy('AccountNumber',$account);
		}

		$amount = round($amount,2);
		
		if($account['branch_id'] != $this['branch_id']){
			$this->all_credit_accounts_are_mine = false;
			$this->other_branches_involved[$account['branch_id']] = $account->ref('branch_id');
		}

		$this->cr_accounts += array($account['AccountNumber']=>array('amount'=>$amount,'account'=>$account));
	}

	function execute(){
		if($this->loaded())
			throw $this->exception('New Transaction can only be added on unLoaded Transaction Model ');

		if(!$this->create_called) throw $this->exception('Create Account Function Must Be Called First');
		
		// $this->senitizeTransaction();
		
		if(($msg=$this->isValidTransaction($this->dr_accounts,$this->cr_accounts, $this['transaction_type_id'])) !== true)
			throw $this->exception('Transaction is Not Valid')->addMoreInfo('message',$msg);


		if($this->all_debit_accounts_are_mine and $this->all_credit_accounts_are_mine)
			$this->executeSingleBranch();
		else
			$this->executeInterBranch();

		$this->executed=true;
	}

	function senitizeTransaction(){
		$dr_sum=0;
		$cr_sum=0;
		$cr_delta=0;
		$dr_delta=0;
		
		foreach ($this->dr_accounts as $AccountNumber => $dtl) {
			$original_amount = $dtl['amount'];
			$dtl['amount'] = round($dtl['amount'],3);
			$cr_delta += $original_amount - $dtl['amount'];
			$cr_sum += $dtl['amount'];
		}
		
		foreach ($this->cr_accounts as $AccountNumber => $dtl) {
			$original_amount = $dtl['amount'];
			$dtl['amount'] = round($dtl['amount'],3);
			$dr_delta += $original_amount - $dtl['amount'];
			$dr_sum += $dtl['amount'];
		}

		$delta = $cr_delta - $dr_delta;
		echo $delta;

		if($delta < 0){
			$this->dr_accounts[count($this->dr_accounts)-1]['amount'] -= $delta;
		}

		if($delta > 0){
			$this->cr_accounts[count($this->dr_accounts)-1]['amount'] -= $delta;
		}

	}

	function executeSingleBranch(){

		$this->save();

		$total_debit_amount =0;
		// Foreach Dr add new TransacionRow (Dr wali)
		foreach ($this->dr_accounts as $accountNumber => $dtl) {
			if($dtl['amount'] ==0) continue;
			$dtl['account']->debitWithTransaction($dtl['amount'],$this->id,$this->only_transaction);
			$total_debit_amount += $dtl['amount'];
		}


		$total_credit_amount =0;
		// Foreach Cr add new Transactionrow (Cr Wala)
		foreach ($this->cr_accounts as $accountNumber => $dtl) {
			if($dtl['amount'] ==0) continue;
			$dtl['account']->creditWithTransaction($dtl['amount'],$this->id,$this->only_transaction);
			$total_credit_amount += $dtl['amount'];
		}
		
		// Credit Sum Must Be Equal to Debit Sum
		if($total_debit_amount != $total_credit_amount)
			throw $this->exception('Debit and Credit Must be Same')->addMoreInfo('DebitSum',$total_debit_amount)->addMoreInfo('CreditSum',$total_credit_amount);

	}

	
	function executeInterBranch(){

		$other_branch = array_values($this->other_branches_involved);
		$other_branch = $other_branch[0];		

		$my_transaction = $this->add('Model_Transaction');
		$my_transaction->createNewTransaction($this->transaction_type,$this->ref('branch_id'),$this->transaction_date,$this->Narration,$this->only_transaction,$this->options);

		$other_transaction = $this->add('Model_Transaction');
		$other_transaction->createNewTransaction($this->transaction_type,$other_branch,$this->transaction_date,$this->Narration,$this->only_transaction,$this->options);

		$my_branch_and_division_account = $other_branch['Code'] . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $this->ref('branch_id')->get('Code');
		$other_branch_and_division_account = $this->ref('branch_id')->get('Code') . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $other_branch['Code'];

		$dr_total_amount=0;
		// echo "<pre>";
		// print_r($this->dr_accounts);
		// echo "</pre>";

		// echo "<pre>";
		// print_r($this->cr_accounts);
		// echo "</pre>";
		foreach ($this->dr_accounts as $accountNumber=>$dtl) {
			// echo "dr " .$accountNumber .' in '. $dtl['account']['branch_id'] .' while i m in '. $this['branch_id'].'<br/>';
			$dr_total_amount += $dtl['amount'];
			// if($this->all_debit_accounts_are_mine){
			// 	$my_transaction->addDebitAccount($dtl['account'],$dtl['amount']);
			// }
			// else{
				if($dtl['account']['branch_id'] != $this['branch_id']){
					$my_transaction->addDebitAccount($my_branch_and_division_account,$dtl['amount']);
					$other_transaction->addCreditAccount($other_branch_and_division_account,$dtl['amount']);
					$other_transaction->addDebitAccount($dtl['account'],$dtl['amount']);
				}else{
					$my_transaction->addDebitAccount($dtl['account'],$dtl['amount']);
				}
			// }
		}

		
		// if($this->all_debit_accounts_are_mine)
		// 	$my_transaction->addCreditAccount($my_branch_and_division_account,$dr_total_amount);
		// else
		// 	$my_transaction->addDebitAccount($my_branch_and_division_account,$dr_total_amount);
			

		// One Transaction for other_branch 
		$cr_total_amount = 0;
		
		foreach ($this->cr_accounts as $accountNumber=>$dtl) {
			// echo "cr " .$accountNumber .' in '. $dtl['account']['branch_id'] .' while i m in '. $this['branch_id'].'<br/>';
			$cr_total_amount += $dtl['amount'];
			// if($this->all_credit_accounts_are_mine){
			// 	$my_transaction->addCreditAccount($dtl['account'],$dtl['amount']);
			// }
			// else{
				if($dtl['account']['branch_id'] != $this['branch_id']){
					$my_transaction->addCreditAccount($my_branch_and_division_account,$dtl['amount']);
					$other_transaction->addDebitAccount($other_branch_and_division_account,$dtl['amount']);
					$other_transaction->addCreditAccount($dtl['account'],$dtl['amount']);
				}else{
					$my_transaction->addCreditAccount($dtl['account'],$dtl['amount']);
				}
			// }
		}


		// if($this->all_credit_accounts_are_mine)
		// 	$other_transaction->addCreditAccount($other_branch_and_division_account,$cr_total_amount);		
		// else
		// 	$other_transaction->addDebitAccount($other_branch_and_division_account,$cr_total_amount);
		

		if($dr_total_amount != $cr_total_amount ) throw $this->exception('Inter Branch Transaction must have same amounts');

		$my_transaction->execute();
		$other_transaction->execute();
	}

	function isValidTransaction($DRs, $CRs, $transaction_type_id){
		if(count($DRs) > 1 AND count($CRs) > 1)
			return "Dr and Cr both have multiple accounts";

		if(!count($DRs) or !count($CRs))
			return "Either Dr or Cr accounts are not present. DRs =>".count($DRs). " and CRs =>".count($CRs);

		if(!$this->all_debit_accounts_are_mine and !$this->all_credit_accounts_are_mine)
			return "Dr and Cr both containes other branch accounts";

		if(count($this->other_branches_involved) > 1)
			return "More then one other branch involved";

		return true;
	}

	function delete($forced=false,$revert_accounts_balances=true){
		foreach ($tr=$this->ref('TransactionRow') as $tr_array) {
			$tr->delete($revert_accounts_balances);
		}
		parent::delete();
	}

	function filterBy($SchemeType, $from_date=null,$to_date=null,$branch=null){
		if($this->loaded()) throw $this->exception('Model is already loaded, cannot apply filter');

		$transaction_row_join = $this->join('transaction_row.transaction_id');
		$account_join = $transaction_row_join->join('accounts','account_id');
		$scheme_join = $account_join->join('schemes','scheme_id');
		
		$transaction_row->addField('amountCr');
		$transaction_row->addField('amountDr');
		$scheme_join->addField('SchemeType');

		$this->addCondition('SchemeType',$SchemeType);

		if($from_date)
			$this->addCondition('created_at','>=',$from_date);

		if($to_date)
			$this->addCondition('created_at','<',$to_date);

		if(!$branch) $branch = $this->api->current_branch->id;
		if($branch != 'all')
			$this->addCondition('branch_id',$branch);

	}

	// function __destruct(){
		// if($this->create_called and !$this->executed) throw $this->exception('Transaction created but not executed');
	// }
}