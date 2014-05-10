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
		$this->hasOne('Account','reference_account_id');
		$this->hasOne('Branch','branch_id');
		$this->addField('voucher_no_original')->type('int'); //TODO bigint
		$this->addField('voucher_no')->type('int'); //TODO bigint -- Actual Display Voucher
		$this->addField('Narration')->type('text');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);

		$this->hasMany('TransactionRow','transaction_id');
		
		$this->addHook('beforeSave',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
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
		$this['reference_account_id'] = isset($options['reference_account_id'])?:0;
		$this['branch_id'] = $branch->id;
		$this['voucher_no'] = $branch->newVoucherNumber();
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
		
		if($account['branch_id'] != $this['branch_id']){
			$this->all_debit_accounts_are_mine = false;
			$this->other_branches_involved[$account['branch_id']] = 1;
		}

		$this->dr_accounts += array($account=>$amount);
	}

	function addCreditAccount($account, $amount){
		if(is_string($account)){
			$account = $this->add('Model_Account')->loadBy('AccountNumber',$account);
		}
		
		if($account['branch_id'] != $this['branch_id']){
			$this->all_credit_accounts_are_mine = false;
			$this->other_branches_involved[$account['branch_id']] = $account->ref('branch_id');
		}

		$this->cr_accounts += array($account=>$amount);
	}

	function execute(){
		if($this->loaded())
			throw $this->exception('New Transaction can only be added on unLoaded Transaction Model ');

		if(!$this->create_called) throw $this->exception('Create Account Function Must Be Called First');
		
		if(!$this->isValidTransaction($this->dr_accounts,$this->cr_accounts, $this['transaction_type_id']))
			throw $this->exception('Transaction is Not Valid');

		if($this->all_debit_accounts_are_mine and $this->all_credit_accounts_are_mine)
			$this->executeSingleBranch();
		else
			$this->executeInterBranch();

		$this->executed=true;
	}


	function executeSingleBranch(){

		$this->save();

		$total_debit_amount =0;
		// Foreach Dr add new TransacionRow (Dr wali)
		foreach ($this->dr_accounts as $account => $Amount) {
			if($Amount ==0) continue;
			$account->debitWithTransaction($Amount,$this->id,$this->only_transaction);
			$total_debit_amount += $Amount;
		}


		$total_credit_amount =0;
		// Foreach Cr add new Transactionrow (Cr Wala)
		foreach ($this->cr_accounts as $account => $Amount) {
			if($Amount ==0) continue;
			$account->creditWithTransaction($Amount,$this->id,$this->only_transaction);
			$total_credit_amount += $Amount;
		}
		
		// Credit Sum Must Be Equal to Debit Sum
		if($total_debit_amount != $total_credit_amount)
			throw $this->exception('Debit and Credit Must be Same')->addMoreInfo('DebitSum',$total_debit_amount)->addMoreInfo('CreditSum',$total_credit_amount);

	}

	
	function executeInterBranch(){

		$other_branch = array_values($this->other_branches_involved);
		$other_branch = $other_branch[0];

		$my_transaction = $this->add('Model_Transaction');
		$my_transaction->createNewTransaction($this->transaction_type,null,$this->transaction_date,$this->Narration,$this->only_transaction,$this->options);

		$other_transaction = $this->add('Model_Transaction');
		$other_transaction->createNewTransaction($this->transaction_type,$other_branch,$this->transaction_date,$this->Narration,$this->only_transaction,$this->options);

		$dr_total_amount=0;
		
		foreach ($this->dr_accounts as $acc=>$amt) {
			$dr_total_amount += $amt;
			if($this->all_debit_accounts_are_mine){
				$my_transaction->addDebitAccount($acc,$amt);
			}
			else{
				$other_transaction->addDebitAccount($acc,$amt);
			}
		}

		$my_branch_and_division_account = $this->other_branch['Code'] . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $this->api->current_branch['Code'];
		
		if($this->all_debit_accounts_are_mine)
			$my_transaction->addCreditAccount($my_branch_and_division_account,$dr_total_amount);
		else
			$my_transaction->addDebitAccount($my_branch_and_division_account,$dr_total_amount);
			

		// One Transaction for other_branch 
		$cr_total_amount = 0;
		
		foreach ($this->cr_accounts as $acc=>$amt) {
			$cr_total_amount += $amt;
			if($this->all_credit_accounts_are_mine){
				$my_transaction->addCreditAccount($acc,$amt);
			}
			else{
				$other_transaction->addCreditAccount($acc,$amt);
			}
		}

		$other_branch_and_division_account = $this->api->current_branch['Code'] . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $this->other_branch['Code'];

		if($this->all_credit_accounts_are_mine)
			$my_transaction->addDebitAccount($other_branch_and_division_account,$cr_total_amount);
		else
			$other_transaction->addCreditAccount($other_branch_and_division_account,$cr_total_amount);		
		

		if($dr_total_amount != $cr_total_amount ) throw $this->exception('Inter Branch Transaction must have same amounts');

		$my_transaction->execute();
		$other_transaction->execute();
	}

	function isValidTransaction($DRs, $CRs, $transaction_type_id){
		if(count($DRs) > 1 AND count($CRs) > 1)
			return false;

		if(!count($DRs) or !count($CRs))
			return false;

		if(!$this->all_debit_accounts_are_mine and !$this->all_credit_accounts_are_mine)
			return false;

		if(count($this->other_branches_involved) > 1)
			return false;

		return true;
	}

	function __destruct(){
		if(!$this->executed) throw $this->exception('Transaction created but not executed');
	}
}