<?php

class page_memorandum_deposite extends Page{
	public $title = "Memorandum Deposite";

	function init(){
		parent::init();

		$model_memo_tran = $this->add('Model_Memorandum_Transaction');

		$col = $this->add('Columns');
		$col1 = $col->addColumn(6);

		$form = $col1->add('Form');
		$form->addField('DropDown','transaction_type')
			->setValueList($model_memo_tran->getTransactionType())
			->setEmptyText('Please Select ...')
			->validateNotNull();

		$model_account = $this->add('Model_Account')->addCondition('branch_id',$this->app->current_branch->id);
		$form->addField('autocomplete/Basic','amount_from_account')->validateNotNull()->setModel($model_account);
		// $form->addField('DropDown','tax')->setValueList(GST_VALUES)->validateNotNull();
		$form->addField('amount')->validateNotNull();
		$form->addField('checkbox','by_cheque')->set(false);
		$form->addField('text','narration');
		$form->addSubmit('Submit');

		$form->add('misc\Controller_FormAsterisk');

		if($form->isSubmitted()){
			
			$this->setTransactionData($form->get());

			throw new \Exception("Error Processing Request", 1);

			try{
				$this->api->db->beginTransaction();
				// Create Actual Transaction CREDIT
					$transaction->createNewTransaction($tra_data['transaction_credit']['transaction_type'],$this->api->currentBranch,$this->app->now,$form['narration'],null,['reference_id'=>$form['amount_from_account']]);
					//amount from account credit
					$transaction->addCreditAccount($account_cr,$form['amount']);
					//charge ie(visit, etc), gst are debit
					$transaction->addDebitAccount($account_dr,$form['amount']);
					$transaction->execute();
				// end of Actual Transaction CREDIT ----------------------

				// Create Actual Transaction DEBIT
					// $account_dr = $this->add('Model_Account')->loadBy('AccountNumber',$form['amount_from_account']);
					// $account_cr = $this->add('Model_Account')->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.$transaction_array[$form['type']][0]);
					// $transaction = $this->add('Model_Transaction');
					// $transaction->createNewTransaction($transaction_array[$form['type']][1],$this->api->currentBranch,$this->app->now,$form['narration'],null,['reference_id'=>$account_dr->id]);
					// $transaction->addDebitAccount($account_dr,$form['amount']);
					// $transaction->addCreditAccount($account_cr,$form['amount']);
					// $transaction->execute();
					// $account_dr['is_'.$form['type']]=true;
					// $account_dr[$form['type'].'_on']=$this->app->now;
					// $account_dr->save();
				// end of Actual Transaction DEBIT -----------------------

				// Create Memorandum Amount Received
				// end of Memorandum Amount Received----------------------
				$this->api->db->commit();
			}catch(\Exception $e){
				$this->api->db->rollback();
			}

			// $model_memo_tran->createNewTransaction(null,$form['transaction_type'],$form['narration'],$row_data);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Saved Successfully')->execute();

		}
	}


	function setTransactionData($form){
		$tra_array = MEMORANDUM_ACCOUNT_TRA_ARRAY;
		$transaction_type = $tra_array[$form['transaction_type']][0];

		$tax_percentage = 18;
		$tax = (100 + $tax_percentage);
		$tax_excluded_amount = round((($form['amount']/$tax)*100),2);
		$tax_amount = round(($form['amount'] - $tax_excluded_amount),2);

		$charge_account_number = $this->api->currentBranch['Code'].SP.$tra_array[$form['transaction_type']][0];
		$charge_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$charge_account_number);
		$charge_account_model->tryLoadAny();
		if(!$charge_account_model->loaded()) throw new \Exception("Account Not Found ".$charge_account_number);

		$sgst_account_number = $this->api->currentBranch['Code'].SP."SGST 9%";
		$cgst_account_number = $this->api->currentBranch['Code'].SP."CGST 9%";

		$gst_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$sgst_account_number);
		$gst_account_model->tryLoadAny();
		if(!$gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$sgst_account_number." )");
		$sgst_account_number_id = $gst_account_model->id;

		$gst_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$cgst_account_number);
		$gst_account_model->tryLoadAny();
		if(!$gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$cgst_account_number." )");
		$cgst_account_number_id = $gst_account_model->id;

		$cash_default = $this->add('Model_Account')->addCondition('AccountNumber',$this->app->currentBranch['Code'].SP.CASH_ACCOUNT_SCHEME)->tryLoadAny();
		if(!$cash_default->loaded()) throw new \Exception("Default Cash Account Not Found" .$this->app->currentBranch['Code'].SP.CASH_ACCOUNT_SCHEME);
		$default_cash_account_id = $cash_default->id;

		$this->data = $data = [
				'transaction_credit'=>[
						'transaction_type'=>$transaction_type,
						'account_cr'=>['id'=>$form['amount_from_account'],'amount'=>$form['amount']],
						'account_dr'=>[
								'charge_account'=>['id'=>$charge_account_model->id,'amount'=>$tax_excluded_amount],
								'cgst'=>['id'=>$cgst_account_number_id,'amount'=>round(($tax_amount/2),2)],
								'sgst'=>['id'=>$sgst_account_number_id,'amount'=>round(($tax_amount/2),2)],
							],
					],
				'transaction_debit'=>[
						'account_cr'=>['id'=>$default_cash_account_id,'amount'=>$form['amount']],
						'account_dr'=>['id'=>$form['amount_from_account'],'amount'=>$form['amount']]
					],
				'memorandum_debit'=>[
						'account_cr'=>['id'=>$default_cash_account_id,'amount'=>$form['amount']],
						'account_dr'=>['id'=>$form['amount_from_account'],'amount'=>$form['amount']]
					],
			];

		return $data;
	}

}