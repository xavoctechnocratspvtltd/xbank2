<?php

// ========== ONLY FOR LOAN ACCOUNTS ==========

class Model_PendingAccount extends Model_Account_Loan {
	var $table= "accounts_pending";

	function init(){
		parent::init();
		$this->addField('is_approved')->type('boolean')->defaultValue(false);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function approve(){
		if(!$this->loaded()) throw $this->exception('Pending Account must be loaded to approve');

		$model="";

		if(in_array($this['account_type'],explode(",",LOAN_TYPES)))
			$model="_Loan";
		elseif (in_array($this['account_type'],array('FD','MIS'))) {
			$model='_Recurring';
		}elseif (in_array($this['account_type'], array('Saving','Current'))) {
			$model= "_SavingAndCurrrent";
		}else{
			$model='_'.$this['account_type'];
		}

		$extra_info = json_decode($this['extra_info'],true);
		
		$account_model = $this->add('Model_Account');
		$account_model->load($extra_info['loan_from_account']);
		$op_bal = $account_model->getOpeningBalance($this->api->nextDate($this->api->today));

		$op_bal = $op_bal['dr']-$op_bal['cr'];

		if( $account_model->isBank() AND (($op_bal - $this['Amount']) <= 0 ) ){
			$this->api->js()->univ()->errorMessage('Not Sufficient Balance as on Date, Current Balance is ' . $op_bal . ' /-')->execute();
		}		

		$new_account = $this->add('Model_Account'.$model);
		$otherValues = $this->data;
		$otherValues['loan_from_account'] = $extra_info['loan_from_account'];
		$otherValues['sm_amount'] = $extra_info['sm_amount'];
		$otherValues['other_account'] = $extra_info['other_account'];
		$otherValues['other_account_cr_amount'] = $extra_info['other_account_cr_amount'];
		
		unset($otherValues['id']);

		$new_account->createNewAccount($this['member_id'],$this['scheme_id'],$this->ref('branch_id'), $new_account->getNewAccountNumber($this['account_type'],$this->ref('branch_id')) ,$otherValues,$form=null, $on_date = null );

		$this['is_approved'] = true;
		$this->save();

		return $new_account;
	}

	function reject(){
		// $this->addHook('beforeDelete',function($obj){$obj->breakHook(true);});
		$this->delete();
	}
}