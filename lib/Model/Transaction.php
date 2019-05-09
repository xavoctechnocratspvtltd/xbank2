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

	public $transaction_type;
	public $branch;
	public $transaction_date;
	public $Narration;
	public $options;

	function init(){
		parent::init();

		$this->hasOne('TransactionType','transaction_type_id')->display(['form'=>'autocomplete/Basic']);;
		$this->hasOne('Staff','staff_id')->display(['form'=>'autocomplete/Basic']);;
		$this->hasOne('Account','reference_id')->display(['form'=>'autocomplete/Basic']);;
		$this->hasOne('Branch','branch_id')->display(['form'=>'autocomplete/Basic']);;
		$this->addField('voucher_no_original')->type('int'); //TODO bigint
		$this->addField('voucher_no'); //Double as back date vouchers are now .1 .2 etc
		$this->addField('Narration')->type('text');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);

		$this->addField('invoice_no');
		$this->addField('is_sale_invoice')->type('boolean')->defaultValue(false);
		$this->addField('is_invoice_cancel')->type('boolean')->defaultValue(false);

		$this->hasMany('TransactionRow','transaction_id');

		$this->addExpression('cr_sum')->set(function($m,$q){
			return $m->refSQL('TransactionRow')->sum('amountCr');
		});

		$this->addExpression('dr_sum')->set(function($m,$q){
			return $m->refSQL('TransactionRow')->sum('amountDr');
		});
		
		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		$this['staff_id'] = $this->api->auth->model->id;
		$this['updated_at'] = $this->api->now;

		// Check Exisintg Voucher

		$old_tra = $this->add('Model_Transaction');
		$old_tra->addCondition('branch_id',$this['branch_id']);
		$old_tra->addCondition('voucher_no',$this['voucher_no']);

		$f_year = $this->api->getFinancialYear($this['created_at']);	
		$start_date = $f_year['start_date'];
		$end_date = $f_year['end_date'];

		$old_tra->addCondition('created_at','>',$start_date);
		$old_tra->addCondition('created_at','<=',$this->api->nextDate($start_date));

		$old_tra->tryLoadAny();

		if($old_tra->loaded()){
			throw $this->exception('Voucher No '. $this['voucher_no']. ' already used')
					->addMoreInfo('branch',$this['branch'])
					->addMoreInfo('voucher_no',$this['voucher_no'])
					->addMoreInfo('start_date',$start_date)
					->addMoreInfo('end_date',$this->api->nextDate($start_date))
					->addMoreInfo('transaction_date',$this['created_at'])
					->addMoreInfo('related_account',$this['reference'])
					->addMoreInfo('transaction_type',$this['transaction_type'])
					;
		}

	}

	function beforeDelete(){
		if($this->ref('TransactionRow')->count()->getOne() > 0 )
			throw $this->exception('TRansaction Contains Rows .. Cannot Delete');
	}

	function rows(){
		return $this->ref('TransactionRow');
	}

	function createNewTransaction($transaction_type, $branch=null, $transaction_date=null, $Narration=null, $only_transaction=null,$options=array()){
		if($this->loaded()) throw $this->exception('Use Unloaded Transaction model to create new Transaction');
		
		$transaction_type_model = $this->add('Model_TransactionType');
		$transaction_type_model->tryLoadBy('name',$transaction_type);
		
		if(!$transaction_type_model->loaded()) $transaction_type_model->save();

		if(!$branch) $branch = $this->api->current_branch;

		if(!$transaction_date) $transaction_date = $this->api->now;
		// echo "createNewTransaction on date $transaction_date <br/>";
		// Transaction TYpe Save if not available
		$this['transaction_type_id'] = $transaction_type_model->id;
		$this['reference_id'] = isset($options['reference_id'])?$options['reference_id']:0;
		$this['branch_id'] = $branch->id;
		$this['voucher_no'] = $branch->newVoucherNumber($branch,$transaction_date);
		$this['Narration'] = $Narration;
		$this['created_at'] = $transaction_date;		
		// new field aded for both purchase and sale invoice no
		if(isset($options['invoice_no']) && $options['invoice_no']){
			$this['invoice_no'] = $options['invoice_no'];
			
			if(isset($options['is_sale_invoice'])) $this['is_sale_invoice'] = $options['is_sale_invoice'];
			else $this['is_sale_invoice'] = 1;
		} 

		$this->transaction_type = $transaction_type;
		$this->branch = $branch;
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

		$amount = round($amount,3);
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

		$amount = round($amount,3);
		
		if($account['branch_id'] != $this['branch_id']){
			$this->all_credit_accounts_are_mine = false;
			$this->other_branches_involved[$account['branch_id']] = $account->ref('branch_id');
		}

		$this->cr_accounts += array($account['AccountNumber']=>array('amount'=>$amount,'account'=>$account));
	}

	function execute($debug=false){
		if($this->loaded())
			throw $this->exception('New Transaction can only be added on unLoaded Transaction Model ');

		if(!$this->create_called) throw $this->exception('Create Account Function Must Be Called First');
		
		$this->senitizeTransaction();
		
		if(($msg=$this->isValidTransaction($this->dr_accounts,$this->cr_accounts, $this['transaction_type_id'])) !== true){	
			$dr_info=array();
			foreach ($this->dr_accounts as $acc_no => $details) {
				$dr_info[$acc_no] = $details['account']['branch'];
			}

			$cr_info=array();
			foreach ($this->cr_accounts as $acc_no => $details) {
				$cr_info[$acc_no] = $details['account']['branch'];
			}

			throw $this->exception('Transaction is Not Valid')
					->addMoreInfo('message',$msg)
					->addMoreInfo('account',$this['reference'])
					->addMoreInfo('dr_account',print_r($dr_info,true))
					->addMoreInfo('cr_account',print_r($cr_info,true))
					->addMoreInfo('my_branch',$this['branch_id'])
					;
		}

		try{
			$this->api->db->beginTransaction();
			if($this->all_debit_accounts_are_mine and $this->all_credit_accounts_are_mine)
				$this->executeSingleBranch($debug);
			else
				$this->executeInterBranch($debug);
			$this->api->db->commit();
		}catch(Exception $e){
			$this->api->db->rollback();
			throw $e;
		}

		$this->executed=true;
		$id = $this->id;
		$this->destroy();
		return $id;
	}

	function senitizeTransaction(){
		$dr_sum=0;
		$cr_sum=0;
		
		$cr_original_sum=0;
		$dr_original_sum=0;

		$cr_delta=0;
		$dr_delta=0;
		
		foreach ($this->dr_accounts as $AccountNumber => $dtl) {
			$original_amount = $dtl['amount'];
			$dtl['amount'] = round($dtl['amount'],3);
			$cr_delta += $original_amount - $dtl['amount'];
			$cr_original_sum += $original_amount;
			$cr_sum += $dtl['amount'];
			// echo $AccountNumber . ' ' . $dtl['amount'] . '<br/>';
		}
		
		foreach ($this->cr_accounts as $AccountNumber => $dtl) {
			$original_amount = $dtl['amount'];
			$dtl['amount'] = round($dtl['amount'],3);
			$dr_delta += $original_amount - $dtl['amount'];
			$dr_original_sum += $original_amount;
			$dr_sum += $dtl['amount'];
			// echo $AccountNumber . ' ' . $dtl['amount'] . '<br/>';
		}

		$delta = $dr_sum - $cr_sum;

		// echo $delta . " delta <br/>";

		if($delta > 0 and $delta < 1){
			foreach ($this->dr_accounts as $AccountNumber => &$dtl) {
				$this->dr_accounts[$AccountNumber]['amount'] = $this->dr_accounts[$AccountNumber]['amount'] - $delta;
				break;
			}
		}

		if($delta < 0 and $delta > -1){
			foreach ($this->cr_accounts as $AccountNumber => &$dtl) {
				$this->cr_accounts[$AccountNumber]['amount'] = $this->cr_accounts[$AccountNumber]['amount'] - $delta;
				break;
			}
		}
	}

	function executeSingleBranch($debug=false){

		if(!count($this->dr_accounts) OR !count($this->cr_accounts))
			return;

		$this->save();

		// echo "transaction saved data <pre>";
		// print_r($this->data);
		// echo "</pre>";

		if($debug){
			foreach ($this->dr_accounts as $accountNumber => &$dtl) {
				unset($dtl['account']);
			}
			foreach ($this->cr_accounts as $accountNumber => &$dtl) {
				unset($dtl['account']);
			}
			var_dump($this->dr_accounts);
			var_dump($this->cr_accounts);
			return;
		}

		$total_debit_amount =0;
		// Foreach Dr add new TransacionRow (Dr wali)
		foreach ($this->dr_accounts as $accountNumber => $dtl) {
			if($dtl['amount'] ==0) continue;
			$dtl['account']->debitWithTransaction($dtl['amount'],$this->id,$this->only_transaction,null,$this->transaction_date);
			$total_debit_amount += $dtl['amount'];
		}


		$total_credit_amount =0;
		// Foreach Cr add new Transactionrow (Cr Wala)
		foreach ($this->cr_accounts as $accountNumber => $dtl) {
			if($dtl['amount'] ==0) continue;
			// if(!$dtl['account'] instanceof Model_Account) echo $accountNumber .' --= problem';
			$dtl['account']->creditWithTransaction($dtl['amount'],$this->id,$this->only_transaction, null,$this->transaction_date);
			$total_credit_amount += $dtl['amount'];
		}
		
		// Credit Sum Must Be Equal to Debit Sum
		if((string)$total_debit_amount != (string)$total_credit_amount)
			throw $this->exception('Debit and Credit Must be Same')->addMoreInfo('DebitSum',$total_debit_amount)->addMoreInfo('CreditSum',$total_credit_amount)->addMoreInfo('Transaction',$this->transaction_type)->addMoreInfo('difference',($total_debit_amount - $total_credit_amount))->addMoreInfo('reference_id',$this->options['reference_id']);

	}

	
	function executeInterBranch($debug=false){

		if(!count($this->dr_accounts) OR !count($this->cr_accounts))
			return;
		
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
		

		if(round($dr_total_amount,8) != round($cr_total_amount,8)){
			var_dump($dr_total_amount);
			var_dump($cr_total_amount);
			throw $this->exception('Inter Branch Transaction must have same amounts')
						->addMoreInfo('dr_total_amount',$dr_total_amount)
						->addMoreInfo('cr_total_amount',$cr_total_amount)
						->addMoreInfo('transaction_type',$this['transaction_type']);
						;
		} 

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

	function forceDelete(){
		foreach ($tr=$this->ref('TransactionRow') as $tr_array) {
			$tr->forceDelete();
		}
		
		$this->delete();
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

	function referenceAccount(){
		$temp = $this->ref('reference_id');
		if($temp->loaded()) return $temp;

		return false;
	}

	function newInvoiceNumber($transaction_date=null){

		$f_year = $this->api->getFinancialYear($transaction_date);
		$start_date = $f_year['start_date'];
		$end_date=$transaction_date;

		$transaction_model = $this->add('Model_Transaction');
		$transaction_model->addCondition('created_at','>=',$start_date);
		$transaction_model->addCondition('created_at','<',$this->api->nextDate($end_date)); // ! important next date
		$transaction_model->addCondition('is_sale_invoice',1);

		$last_inv_no = $transaction_model->_dsql()->del('fields')->field('max(CAST(invoice_no AS int))')->getOne();
		$new_inv_no = $last_inv_no + 1;
		if($new_inv_no < 766) return 767;
		
		return $new_inv_no;
	}

	// function __destruct(){
		// if($this->create_called and !$this->executed) throw $this->exception('Transaction created but not executed');
	// }
}