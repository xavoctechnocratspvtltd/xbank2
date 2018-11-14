<?php

class page_transactions_salaryandallowances extends Page {
	
	public $title ='Salary And Allowances';

	function init(){
		parent::init();

		echo "Under construction";
		return;
		
		$this->add('Controller_Acl');

		$salary_account = $this->add('Model_Account');
		$salary_account->addCondition('AccountNumber','like','%SALARY%');

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','salary_account')->validateNotNull()->setModel($salary_account);
		
		$account_from_account_model = $this->add('Model_Active_Account',array('table_alias'=>'acc'));
		$account_from_account_model->addCondition([['SchemeType',ACCOUNT_TYPE_BANK],['SchemeType',ACCOUNT_TYPE_SAVING]]);
		$account_from_account_model->add('Controller_Acl');

		$form->addField('Number','net_salary')->validateNotNull();
		$form->addField('autocomplete/Basic','submitted_into')->validateNotNull()->setModel($account_from_account_model);
		$form->addField('Number','tds');
		$form->addField('Number','pf');
		$form->addField('Number','health_insurance');
		$form->addField('Number','security');

		$form->addField('Text','narration');
		$form->addSubmit('Execute Salary Transaction');

		if($form->isSubmitted()){
			try {
				$this->api->db->beginTransaction();
				$dr = $this->add('Model_Account')->load($form['salary_account']);
				$dr_amount=0;

				$transaction = $this->add('Model_Transaction');
				$transaction->createNewTransaction(TRA_SALARY_AND_ALLOWENCES,$dr->ref('branch_id'),$this->app->now,$form['narration']);
				
				if($form['net_salary']){
					$transaction->addCreditAccount($this->add('Model_Account')->load($form['submitted_into']),$form['net_salary']);
					$dr_amount += $form['net_salary'];
				}

				if($form['tds']){
					$transaction->addCreditAccount($dr['branch_code'].SP.BRANCH_TDS_ACCOUNT,$form['tds']);
					$dr_amount += $form['tds'];
				}

				if($form['pf']){
					$transaction->addCreditAccount($dr['branch_code'].SP.'PF ACCOUNT',$form['pf']);
					$dr_amount += $form['pf'];
				}

				if($form['health_insurance']){
					$transaction->addCreditAccount($dr['branch_code'].SP.'INSURANCE ON STAFF (STAR HEALTH)',$form['health_insurance']);
					$dr_amount += $form['health_insurance'];
				}

				if($form['security']){
					$transaction->addCreditAccount($dr['branch_code'].SP.'STAFF SECURITY',$form['security']);
					$dr_amount += $form['security'];
				}

				$transaction->addDebitAccount($dr,$dr_amount);
				$transaction->execute();
				$this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}

			$form->js(null,$form->js()->univ()->successMessage('Done'))->reload()->execute();
			
		}
	}
}