<?php
class Model_Supplier extends Model_Table {
	var $table= "supplier";

	function init(){
		parent::init();

		$this->addField('name')->caption('Full Name');
		$this->addField('organization');
		$this->addField('gstin');
		$this->addField('address')->type('text');
		$this->addField('email_ids')->type('text')->hint('comma (,) seperated multiple values');
		$this->addField('phone_nos')->type('text')->hint('comma (,) seperated multiple values');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}


	function createPurchaseTransaction($data){
		if(!$this->loaded()) throw new \Exception("supplier model must loaded");
		
		$in_branch = $this->api->current_branch;
		$transaction_date = $this->api->now;
		$narration = "Purchase Entry";

		$account_cr = $this->add('Model_Account')->load($amount_from_account);
		$account_dr = ;
		
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_PURCHASE_ENTRY,$in_branch,$transaction_date,$narration,null,array('reference_id'=>$account_cr->id));
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);
		$transaction->execute();
	}

}