<?php
class Model_Transaction extends Model_Table {
	var $table= "transactions";
	function init(){
		parent::init();

		$this->hasOne('TransactionType','transaction_type_id');
		$this->hasOne('Staff','staff_id');
		$this->hasOne('Account','reference_account_id');
		$this->hasOne('Branch','branch_id');
		$this->addField('voucher_no')->type('int'); //TODO bigint
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


	function doTransaction($DRs, $CRs, $transaction_type, $branch_id=null, $transaction_date=null, $Narration=null, $only_transaction=false, $options=array() ){
		if($this->loaded())
			throw $this->exception('New Transaction can only be added on unLoaded Transaction Model ');

		if(!$this->isValidTransaction($DRs,$CRs, $transaction_type_id))
			throw $this->exception('Transaction is Not Valid');

		if(!$branch_id) $branch_id = $this->api->current_branch->id;

		// Transaction Master Save
		$transaction_type = $this->add('Model_TransactionType');
		$transaction_type->tryLoadBy('name',$transaction_type);
		if(!$transaction_type->loaded()) $transaction_type->save();

		$this['transaction_type_id'] = $transaction_type->id;
		$this['reference_account_id'] = isset($options['reference_account_id'])?:0;
		$this['branch_id'] = $branch_id;
		$this['voucher_no'] = $this->api->current_branch->newVoucherNumber();
		$this['Narration'] = $Narration;

		$this->save();

		$total_debit_amount =0;
		// Foreach Dr add new TransacionRow (Dr wali)
		foreach ($DRs as $AccountNumber => $Amount) {
			if($Amount ==0) continue;
			$account = $this->add('Model_Account');
			$account->loadBy('AccountNumber',$AccountNumber);
			$account->debitWithTransaction($Amount,$this->id,$only_transaction);
			$total_debit_amount += $Amount;
		}


		$total_credit_amount =0;
		// Foreach Cr add new Transactionrow (Cr Wala)
		foreach ($CRs as $AccountNumber => $Amount) {
			if($Amount ==0) continue;
			$account = $this->add('Model_Account');
			$account->loadBy('AccountNumber',$AccountNumber);
			$account->creditWithTransaction($Amount,$this->id,$only_transaction);
			$total_credit_amount += $Amount;
		}
		// Credit Sum Must Be Equal to Debit Sum
		
		if($total_debit_amount != $total_credit_amount)
			throw $this->exception('Debit and Credit Must be Same')->addMoreInfo('DebitSum',$total_debit_amount)->addMoreInfo('CreditSum',$total_credit_amount);

	}

	function isValidTransaction($DRs, $CRs, $transaction_type_id){
		if(count($DRs) > 1 AND count($CRs) > 1)
			return false;

		return true;
	}
}