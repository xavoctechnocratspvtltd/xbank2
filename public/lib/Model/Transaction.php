<?php
class Model_Transaction extends Model_Table {
	var $table= "transactions";
	
	public $dr_accounts=array();
	public $cr_accounts=array();

	public $other_branch=null;

	public $my_accounts = array();
	public $myside=null;
	
	public $other_accounts = array();
	public $otherside=null;

	public $only_transaction=false;
	public $create_called=false;

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

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		$this['staff_id'] = $this->api->auth->model->id;
		$this['updated_at'] = $this->api->now;
	}

	function createNewTransaction($transaction_type, $branch=null, $transaction_date=null, $Narration=null, $only_transaction=false){
		if($this->loaded()) throw $this->exception('Use Unloaded Transaction model to create new Transaction');
		
		$transaction_type = $this->add('Model_TransactionType');
		$transaction_type->tryLoadBy('name',$transaction_type);
		if(!$transaction_type->loaded()) $transaction_type->save();

		if(!$branch) $branch = $this->api->current_branch;

		if(!$transaction_date) $transaction_date = $this->api->now;

		// Transaction TYpe Save if not available
		$this['transaction_type_id'] = $transaction_type->id;
		$this['reference_account_id'] = isset($options['reference_account_id'])?:0;
		$this['branch_id'] = $branch->id;
		$this['voucher_no'] = $branch->newVoucherNumber();
		$this['Narration'] = $Narration;
		$this['created_at'] = $transaction_date;

		$this->only_transaction = $only_transaction;
		$this->create_called=true;
	}

	function addDebitAccount($AccountNumber, $amount){
		$this->dr_accounts[] = array($AccountNumber=>$amount);
	}

	function addCreditAccount($AccountNumber, $amount){
		$this->cr_accounts[] = array($AccountNumber=>$amount);
	}

	function executeSingleBranch(){

		if($this->loaded())
			throw $this->exception('New Transaction can only be added on unLoaded Transaction Model ');

		if(!$this->isValidTransaction($this->dr_accounts,$this->cr_accounts, $this['transaction_type_id']))
			throw $this->exception('Transaction is Not Valid');


		$this->save();

		$total_debit_amount =0;
		// Foreach Dr add new TransacionRow (Dr wali)
		foreach ($this->dr_accounts as $AccountNumber => $Amount) {
			if($Amount ==0) continue;
			$account = $this->add('Model_Account');
			$account->loadBy('AccountNumber',$AccountNumber);
			$account->debitWithTransaction($Amount,$this->id,$this->only_transaction);
			$total_debit_amount += $Amount;
		}


		$total_credit_amount =0;
		// Foreach Cr add new Transactionrow (Cr Wala)
		foreach ($cr_accounts as $AccountNumber => $Amount) {
			if($Amount ==0) continue;
			$account = $this->add('Model_Account');
			$account->loadBy('AccountNumber',$AccountNumber);
			$account->creditWithTransaction($Amount,$this->id,$this->only_transaction);
			$total_credit_amount += $Amount;
		}
		
		// Credit Sum Must Be Equal to Debit Sum
		if($total_debit_amount != $total_credit_amount)
			throw $this->exception('Debit and Credit Must be Same')->addMoreInfo('DebitSum',$total_debit_amount)->addMoreInfo('CreditSum',$total_credit_amount);

	}


	function createNewInterBranchTransaction($other_branch,$transaction_type,$transaction_date=null, $Narration=null,$only_transaction=false){
		$this->other_branch = $other_branch;
		$this->my_transaction = $this->add('Model_Transaction');
		$this->my_transaction->createNewTransaction($transaction_type,null,$transaction_date,$Narration,$only_transaction);

		$this->other_transaction = $this->add('Model_Transaction');
		$this->other_transaction->createNewTransaction($transaction_type,$this->other_branch,$transaction_date,$Narration,$only_transaction);

	}

	function addMyAccount($AccountNumber,$amount,$side){
		if(!in_array($side, array("dr","cr"))) throw $this->exception('side must be dr or cr in string small letters');
		if( $this->myside!=null and $this->myside!=$side) throw $this->exception('My Accounts Must be in single sides all must be wither DR or CR');
		$this->myside = $side;
		$this->my_accounts[] = array($AccountNumber=>$amount);
	}

	function addOtherAccount($AccountNumber,$amount, $side){
		if(!in_array($side, array("dr","cr"))) throw $this->exception('side must be dr or cr in string small letters');
		if(isset($this->otherside) and $this->otherside!=$side) throw $this->exception('My Accounts Must be in single sides all must be wither DR or CR');
		
		$this->otherside = $side;
		$this->other_accounts[] =array($AccountNumber=>$amount);
	}

	function execute(){
		if(!$this->create_called) throw $this->exception('Create Account Function Must Be Called First');
		
		if($this->other_branch == null)
			$this->executeSingleBranch();
		else
			$this->executeInterBranch();
	}

	
	function executeInterBranch(){
	
		$my_total_amount=0;
		
		foreach ($this->my_accounts as $acc=>$amt) {
			$my_total_amount += $amt;
			if($this->myside=='dr'){
				$this->my_transaction->addDebitAccount($acc,$amt);
			}
			else{
				$this->my_transaction->addCreditAccount($acc,$amt);
			}
		}

		$my_branch_and_division_account = $this->other_branch['Code'] . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $this->api->current_branch['Code'];

		if($this->myside=='dr')
			$this->my_transaction->addCreditAccount($my_branch_and_division_account,$my_total_amount);
		else
			$this->my_transaction->addDebitAccount($my_branch_and_division_account,$my_total_amount);


		// One Transaction for other_branch 
		$other_total_amount = 0;
		
		foreach ($this->other_accounts as $acc=>$amt) {
			$other_total_amount += $amt;
			if($this->otherside == 'dr'){
				$this->other_transaction->addDebitAccount($acc,$amt);
			}
			else{
				$this->other_transaction->addCreditAccount($acc,$amt);
			}
		}

		$other_branch_and_division_account = $this->api->current_branch['Code'] . SP . BRANCH_AND_DIVISIONS . SP . "for" . SP . $this->other_branch['Code'];

		if($this->otherside == 'dr')
			$this->other_transaction->addCreditAccount($other_branch_and_division_account,$other_total_amount);
		else
			$this->other_transaction->addDebitAccount($other_branch_and_division_account,$other_total_amount);		
		

		if($my_total_amount != $other_total_amount ) throw $this->exception('Inter Branch Transaction must have same amounts');

		$this->my_transaction->execute();
		$this->other_transaction->execute();
	}

	function isValidTransaction($DRs, $CRs, $transaction_type_id){
		if(count($DRs) > 1 AND count($CRs) > 1)
			return false;

		return true;
	}
}