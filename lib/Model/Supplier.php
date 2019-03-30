<?php
class Model_Supplier extends Model_Table {
	var $table= "supplier";

	function init(){
		parent::init();

		$this->hasOne('Account_Default','account_id')->display(['form'=>'autocomplete/Basic']);
		$this->addField('name')->caption('Full Name');
		$this->addField('organization');
		$this->addField('gstin');
		$this->addField('address')->type('text');
		$this->addField('email_ids')->type('text')->hint('comma (,) seperated multiple values');
		$this->addField('phone_nos')->type('text')->hint('comma (,) seperated multiple values');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}



	/* Data format 
		'cr'=>['account_id'=>0,'amount'=>0],
    	[dr] => Array(
				[account] => Array(
                    [ac_id] => amount
                	)
            	[gst] => Array(
            		[GST 18] => tax_amount
                	[IGST 28] => tax_amount)
              		)
    	[total_amount] => 1770
    */

	function createPurchaseTransaction($data){
		if(!$this->loaded()) throw new \Exception("supplier model must loaded");
		
		$in_branch = $this->api->current_branch;
		$transaction_date = $this->api->now;
		$narration = $data['narration']?:"Purchase Entry";

		// purchase entry
		$account_cr = $this->add('Model_Account')->load($data['cr']['account_id']);
		
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_PURCHASE_ENTRY,$in_branch,$transaction_date,$narration,null,array('reference_id'=>$account_cr->id,'invoice_no'=>$data['invoice_no']));
		$transaction->addCreditAccount($account_cr,$data['cr']['amount']);

		foreach ($data['dr']['account'] as $ac_id => $amount){
			$account_dr = $this->add('Model_Account')->load($ac_id);
			$transaction->addDebitAccount($account_dr,$amount);
		}

		foreach ($data['dr']['gst'] as $tax => $tax_amount){

			$tax_array = explode(" ", $tax);
			if($tax_array[0] === "GST"){
				$sgst = $this->api->currentBranch['Code'].SP."SGST ".round(($tax_array[1]/2),1)."%";
				$cgst = $this->api->currentBranch['Code'].SP."CGST ".round(($tax_array[1]/2),1)."%";

				// sgst 
				$account_dr = $this->add('Model_Account')->addCondition('AccountNumber',$sgst)->tryLoadAny();
				if(!$account_dr->loaded()) throw new \Exception($sgst." Account Not Found");
				$transaction->addDebitAccount($account_dr,($tax_amount/2));
				
				// cgst
				$account_dr = $this->add('Model_Account')->addCondition('AccountNumber',$cgst)->tryLoadAny();
				if(!$account_dr->loaded()) throw new \Exception($cgst." Account Not Found");
				$transaction->addDebitAccount($account_dr,($tax_amount/2));

			}elseif($tax_array[0] === "IGST"){
				$igst = $this->api->currentBranch['Code'].SP.$tax."%";
				$account_dr = $this->add('Model_Account')->addCondition('AccountNumber',$igst)->tryLoadAny();
				if(!$account_dr->loaded()) throw new \Exception($igst. " Account Not Found");
				
				$transaction->addDebitAccount($account_dr,$tax_amount);
			}
		}
		$transaction->execute();


		// new entry for tds
		if($data['tds_amount'] > 0){
			$tds_acct = $this->api->currentBranch['Code'].SP.BRANCH_TDS_ACCOUNT;

			$account_cr = $this->add('Model_Account')->addCondition('AccountNumber',$tds_acct)->tryLoadAny();
			$account_dr = $this->add('Model_Account')->load($data['cr']['account_id']);

			$narration = $data['narration']?:"Being TDS Deducted From Supplier ".$account_dr['AccountNumber'];

			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction('TDS',$in_branch,$transaction_date,$narration,null,array('reference_id'=>$account_cr->id,'invoice_no'=>$data['invoice_no']));
			$transaction->addCreditAccount($account_cr,$data['tds_amount']);
			$transaction->addDebitAccount($account_dr,$data['tds_amount']);
			$transaction->execute();
		}
	}

}