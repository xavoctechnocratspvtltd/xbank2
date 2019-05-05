<?php

class page_memorandum_deposite extends Page{
	public $title = "Memorandum Deposite";

	public $transaction_type=null;
	public $tax_excluded_amount=0;
	public $charge_account_number=0;
	public $charge_account_model=null;
	public $sgst_account_model=null;
	public $cgst_account_model=null;
	public $cash_default_model=null;
	public $bank_account=null;
	public $bank_default_model=null;
	public $amount_from_account_model=null;
	public $total_tax_amount=0;
	public $sgst_tax_amount=0;
	public $cgst_tax_amount=0;

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

		$model_account = $this->add('Model_Active_Account')->addCondition('branch_id',$this->app->current_branch->id);
		$form->addField('autocomplete/Basic','amount_from_account')->validateNotNull()->setModel($model_account);
		// $form->addField('DropDown','tax')->setValueList(GST_VALUES)->validateNotNull();
		$form->addField('amount')->validateNotNull();
		$field_bank_cheque_box = $form->addField('checkbox','amount_received_from_bank');
		$field_bank_account = $form->addField('autocomplete\Basic','bank_account');
		$field_bank_account->setModel($model_account);

		$field_bank_cheque_box->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array('bank_account')
					),'div .atk-form-row');

		// $form->addField('text','narration');
		$form->addSubmit('Submit');

		$form->add('misc\Controller_FormAsterisk');

		if($form->isSubmitted()){
			$amount_type = "CASH";

			if($form['amount_received_from_bank'] && !$form['bank_account']){
				$form->displayError('bank_account','Bank Account Must Not Be Empty');
			}
			if($form['amount_received_from_bank']) $amount_type = "BANK";

			$this->setTransactionData($form->get());

			try{
				$this->api->db->beginTransaction();

				// Create Actual Transaction CREDIT
					$narration = "Being ".str_replace("RECEIVED", " ", strtoupper($this->transaction_type))." Debited in ".$this->amount_from_account_model['name'];
					$transaction = $this->add('Model_Transaction');
					$invoice_no = $transaction->newInvoiceNumber($this->app->now);
										
					$transaction->createNewTransaction($this->transaction_type,$this->api->currentBranch,$this->app->now,$narration,null,['reference_id'=>$form['amount_from_account'],'invoice_no'=>$invoice_no]);
					//amount from account credit
					$transaction->addDebitAccount($this->amount_from_account_model,$form['amount']);
					//charge ie(visit, etc), gst are debit
					$transaction->addCreditAccount($this->charge_account_model,$this->tax_excluded_amount);
					$transaction->addCreditAccount($this->sgst_account_model,$this->sgst_tax_amount);
					$transaction->addCreditAccount($this->cgst_account_model,$this->cgst_tax_amount);
					$transaction->execute();
				// end of Actual Transaction CREDIT ----------------------

				// Create Actual Transaction DEBIT
					$narration = "Being ".$amount_type." Deposited in ".$this->amount_from_account_model['name'];
					$transaction = $this->add('Model_Transaction');
					$transaction->createNewTransaction($this->transaction_type,$this->api->currentBranch,$this->app->now,$narration,null,['reference_id'=>$form['amount_from_account']]);

					if($form['amount_received_from_bank'] && $this->bank_account){
						$transaction->addDebitAccount($this->bank_account,$form['amount']);
					}else{
						$transaction->addDebitAccount($this->cash_default_model,$form['amount']);
					}

					$transaction->addCreditAccount($this->amount_from_account_model,$form['amount']);
					$transaction->execute();
					$this->amount_from_account_model['is_'.$form['transaction_type']]=true;
					$this->amount_from_account_model[$form['type'].'_on']=$this->app->now;
					$this->amount_from_account_model->save();
				// end of Actual Transaction DEBIT -----------------------

				// Create Memorandum Amount Received
					$memo_transaction = $this->add('Model_Memorandum_Transaction');
					$row_data = [];

					$dr_account_id = $this->cash_default_model->id;
					if($form['amount_received_from_bank'] && $this->bank_account) $dr_account_id = $this->bank_account->id;

					$row_data[] = [
							'account_id'=>$dr_account_id,
							'amount_dr'=>$form['amount'],
							'amount_cr'=>0,
							'tax'=>0
						];
					$row_data[] = [
							'account_id'=>$this->amount_from_account_model->id,
							'amount_cr'=>$form['amount'],
							'amount_dr'=>0,
							'tax'=>0
						];
					$memo_transaction->createNewTransaction($name=null,$this->transaction_type,$narration ,$row_data);
				// end of Memorandum Amount Received----------------------

				$this->api->db->commit();
			}catch(\Exception $e){
				$this->api->db->rollback();
				throw $e;
			}

			// $model_memo_tran->createNewTransaction(null,$form['transaction_type'],$form['narration'],$row_data);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Saved Successfully')->execute();

		}
	}


	function setTransactionData($form){
		$tra_array = MEMORANDUM_ACCOUNT_TRA_ARRAY;
		$this->transaction_type = $transaction_type = $tra_array[$form['transaction_type']][0];

		$tax_percentage = 18;
		$tax = (100 + $tax_percentage);
		$this->tax_excluded_amount = $tax_excluded_amount = round((($form['amount']/$tax)*100),2);
		$this->total_tax_amount = $tax_amount = round(($form['amount'] - $tax_excluded_amount),2);

		$this->charge_account_number = $charge_account_number = $this->api->currentBranch['Code'].SP.$tra_array[$form['transaction_type']][0];
		$this->charge_account_model = $charge_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$charge_account_number);
		$charge_account_model->tryLoadAny();
		if(!$charge_account_model->loaded()) throw new \Exception("Account Not Found ".$charge_account_number);

		$sgst_account_number = $this->api->currentBranch['Code'].SP."SGST 9%";
		$cgst_account_number = $this->api->currentBranch['Code'].SP."CGST 9%";

		$this->sgst_account_model = $gst_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$sgst_account_number);
		$gst_account_model->tryLoadAny();
		if(!$gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$sgst_account_number." )");
		$sgst_account_number_id = $gst_account_model->id;

		$this->cgst_account_model = $gst_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$cgst_account_number);
		$gst_account_model->tryLoadAny();
		if(!$gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$cgst_account_number." )");
		$cgst_account_number_id = $gst_account_model->id;

		$this->cash_default_model = $cash_default = $this->add('Model_Account')->addCondition('AccountNumber',$this->app->currentBranch['Code'].SP.CASH_ACCOUNT_SCHEME)->tryLoadAny();
		if(!$cash_default->loaded()) throw new \Exception("Default Cash Account Not Found" .$this->app->currentBranch['Code'].SP.CASH_ACCOUNT_SCHEME);
		$default_cash_account_id = $cash_default->id;

		if($form['amount_received_from_bank'] && $form['bank_account']){
			$this->bank_account = $this->add('Model_Account')->load($form['bank_account']);
		}

		$this->amount_from_account_model = $this->add('Model_Account')->load($form['amount_from_account']);

		$this->sgst_tax_amount = $this->cgst_tax_amount = round(($tax_amount/2),2);

		$this->tax_excluded_amount =  round($form['amount'] - ($this->sgst_tax_amount + $this->cgst_tax_amount),2);
		// $this->data = $data = [
		// 		'transaction_credit'=>[
		// 				'transaction_type'=>$transaction_type,
		// 				'account_cr'=>['id'=>$form['amount_from_account'],'amount'=>$form['amount']],
		// 				'account_dr'=>[
		// 						'charge_account'=>['id'=>$charge_account_model->id,'amount'=>$tax_excluded_amount],
		// 						'cgst'=>['id'=>$cgst_account_number_id,'amount'=>$this->sgst_tax_amount],
		// 						'sgst'=>['id'=>$sgst_account_number_id,'amount'=>$this->cgst_tax_amount],
		// 					],
		// 			],
		// 		'transaction_debit'=>[
		// 				'account_cr'=>['id'=>$default_cash_account_id,'amount'=>$form['amount']],
		// 				'account_dr'=>['id'=>$form['amount_from_account'],'amount'=>$form['amount']]
		// 			],
		// 		'memorandum_debit'=>[
		// 				'account_cr'=>['id'=>$default_cash_account_id,'amount'=>$form['amount']],
		// 				'account_dr'=>['id'=>$form['amount_from_account'],'amount'=>$form['amount']]
		// 			],
		// 	];

		// return $data;
	}

}