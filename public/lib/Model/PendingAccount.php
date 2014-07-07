<?php
class Model_PendingAccount extends Model_Account {
	var $table= "accounts_pending";

	function init(){
		parent::init();
		$this->addField('is_approved')->type('boolean')->defaultValue(false);
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

		$new_account = $this->add('Model_Account'.$model);
		$otherValues = $this->data;

		$new_account->createNewAccount($this['member_id'],$this['scheme_id'],$this->ref('branch_id'), $new_account->getNewAccountNumber() ,$otherValues,$form=null, $on_date = $this['created_at'] );

		$this['is_approved'] = true;
		$this->save();
	}

	function reject(){

	}
}