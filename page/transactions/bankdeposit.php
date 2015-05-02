<?php

class page_transactions_bankdeposit extends Page {
	public $title ='Bank Deposit';

	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$bank_account_model = $this->add('Model_Active_Account');

		$bank_account_model->addCondition($this->api->db->dsql()->orExpr()
				->where($bank_account_model->scheme_join->table_alias.'.name',BANK_ACCOUNTS_SCHEME)
				->where($bank_account_model->scheme_join->table_alias.'.name','Bank OD')
				);
		$form = $this->add('Form');
		
		$form->addField('DropDown','bank_account')
			->setEmptyText('Please Select Bank')
			->validateNotNull()
			->setModel($bank_account_model,'AccountNumber');

		$form->addField('Number','amount')->validateNotNull()->belowField()->add('Text')->set('Please Consider Bank Limit Before Depositing');
		$form->addField('line','staff_name');
		$form->addField('Text','narration');
		$form->addSubmit('Deposit Cash');

		if($form->isSubmitted()){
			
			$account_model = $this->add('Model_Account');
			$account_model->load($form['bank_account']);

			try {
				$this->api->db->beginTransaction();
					$narration = 'Being cash deposited in '. $account_model['AccountNumber'] .' by '. $form['staff_name'];
					if ($form['narration']){
						$narration = $form['narration'] . ' - ' . $form['staff_name'];
					}

					$accounts_to_credit = $this->api->current_branch['Code'] .SP . CASH_ACCOUNT;
					$accounts_to_credit = $this->add('Model_Account')->loadBy('AccountNumber',$accounts_to_credit);
					$account_model->transaction_withdraw_type = TRA_BANK_DEPOSIT;	
					$account_model->default_transaction_withdraw_narration = $narration;

					// IMPORTANT ... withdrawl is not by mistake. its CONTRA ENTRY .. so to keep
					// Debit and Credit Ok as required USED WITHDRAWL insted DEPOSIT.
			    	$account_model->withdrawl($form['amount'],$narration,$accounts_to_credit,$form_values=null,$on_date=null,$in_branch=null,$reference_id=null);
			    
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- Deposited in Bank Account " . $form['bank_account'])->execute();
		}
	}
}