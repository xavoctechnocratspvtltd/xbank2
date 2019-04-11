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
			
			
			try{
				$this->api->db->beginTransaction();
				// Create Actual Transaction CREDIT
					$transaction->createNewTransaction($transaction_array[$form['type']][1],$this->api->currentBranch,$this->app->now,$form['narration'],null,['reference_id'=>$account_dr->id]);
					$transaction->addDebitAccount($account_dr,$form['amount']);
					$transaction->addCreditAccount($account_cr,$form['amount']);
					$transaction->execute();
				// end of Actual Transaction CREDIT ----------------------

				// Create Actual Transaction DEBIT
					$account_dr = $this->add('Model_Account')->loadBy('AccountNumber',$form['amount_from_account']);
					$account_cr = $this->add('Model_Account')->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.$transaction_array[$form['type']][0]);
					$transaction = $this->add('Model_Transaction');
					$transaction->createNewTransaction($transaction_array[$form['type']][1],$this->api->currentBranch,$this->app->now,$form['narration'],null,['reference_id'=>$account_dr->id]);
					$transaction->addDebitAccount($account_dr,$form['amount']);
					$transaction->addCreditAccount($account_cr,$form['amount']);
					$transaction->execute();
					$account_dr['is_'.$form['type']]=true;
					$account_dr[$form['type'].'_on']=$this->app->now;
					$account_dr->save();
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
}