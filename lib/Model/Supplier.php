<?php
class Model_Supplier extends Model_Table {
	var $table= "supplier";

	function init(){
		parent::init();

		$this->addField('name')->caption('Full Name')->mandatory(true);
		$this->addField('organization');
		$this->addField('gstin');
		$this->addField('address')->type('text')->mandatory(true);
		$this->addField('email_ids')->type('text')->hint('comma (,) seperated multiple values');
		$this->addField('phone_nos')->type('text')->hint('comma (,) seperated multiple values');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->addHook('afterSave',[$this,'updateSupplierAccounts']);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	/*create/update all supplier accounts in all branch */
	function updateSupplierAccounts(){

		$all_branches = $this->add('Model_Branch')->getRows(['Code']);
		
		$default_scheme_id = $this->add('Model_Scheme')->setActualFields(['name','id'])->loadBy('name','Sundry Creditor')->get('id');
		
		foreach ($all_branches as $branch_array){

			$supp_act_number = $branch_array['Code'].SP.$this['name']." (".$this['organization'].")";
			$default_member_id = $this->add('Model_Member')->setActualFields(['name','id'])->loadBy('name',$branch_array['Code'].SP.'Default')->get('id');
			
			$ac_model = $this->add('Model_Account_Default')->setActualFields(['member_id','scheme_id','related_account_id','related_type','AccountNumber','branch_id']);

			$ac_model->addCondition('related_type','Model_Supplier');
			$ac_model->addCondition('related_type_id',$this->id);
			$ac_model->addCondition('scheme_id',$default_scheme_id);
			$ac_model->addCondition('member_id',$default_member_id);
			$ac_model->addCondition('branch_id',$branch_array['id']);
			$ac_model->tryLoadAny();

			$ac_model['AccountNumber'] = $supp_act_number;
			$ac_model->save();
		}
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
		
		$in_branch = $this->api->current_branch;
		$transaction_date = $this->api->now;
		$narration = $data['narration']?:"Purchase Entry";

		// purchase entry
		$account_cr = $this->add('Model_Account')->load($data['cr']['account_id']);
		
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_PURCHASE_ENTRY,$in_branch,$transaction_date,$narration,null,array('reference_id'=>$account_cr->id,'invoice_no'=>$data['invoice_no'],'is_sale_invoice'=>0));
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