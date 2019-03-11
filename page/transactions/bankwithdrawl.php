<?php

class page_transactions_bankwithdrawl extends Page {
	public $title ='Bank Withdrawl';

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

		$form->addField('Number','amount')->validateNotNull();
		$form->addField('line','staff_name');
		$form->addField('Text','narration');
		$form->addSubmit('Withdrawl Cash');

		if($form->isSubmitted()){
			
			$account_model = $this->add('Model_Account');
			$account_model->load($form['bank_account']);
			
			$op_bal_array = $account_model->getOpeningBalance($this->api->nextDate($this->api->today));
			$op_bal = $op_bal_array['dr']-$op_bal_array['cr'];

			if($account_model['scheme_name'] == "Bank OD"){
				$op_bal += $account_model['bank_account_limit'];
			}
			
			if(($op_bal - $form['amount']) < 0 ){
				$form->displayError('bank_account','Not Sufficient Balance as on Date, Current Balance is ' . $op_bal . ' /- after withdrawl current balance is ' .($op_bal - $form['amount']));
			}
			
			try {
				$this->api->db->beginTransaction();
					$narration = 'Being cash withdrawl from '. $account_model['AccountNumber'] .' by '. $form['staff_name'];
					if ($form['narration']){
						$narration = $form['narration'] . ' - ' . $form['staff_name'];
					}

					$accounts_to_debit = $this->api->current_branch['Code'] .SP . CASH_ACCOUNT;
					// $accounts_to_debit = $this->add('Model_Account')->loadBy('AccountNumber',$accounts_to_debit);
					$account_model->transaction_deposit_type = TRA_BANK_WITHDRAWL;	
					$account_model->default_transaction_deposit_narration = $narration;

					// IMPORTANT ... deposit is not by mistake. its CONTRA ENTRY .. so to keep
					// Debit and Credit Ok as required USED DEPOSIT insted WITHDRAWL.
			    	$account_model->deposit($form['amount'],$narration,array(array($accounts_to_debit=>$form['amount'])),$form_values=null,$on_date=null,$in_branch=null,$reference_id=null);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- Withdrawl from Bank Account " . $form['bank_account'])->execute();
		}
	}
}