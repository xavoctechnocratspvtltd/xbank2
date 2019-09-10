<?php

class page_transactions_salaryandallowances extends Page {
	
	public $title ='Salary And Allowances';

	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$salary_account = $this->add('Model_Account');
		$salary_account->addCondition('AccountNumber','like','%SALARY%');

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','salary_account')->validateNotNull()->setModel($salary_account);
		
		$account_from_account_model = $this->add('Model_Active_Account',array('table_alias'=>'acc'));
		$account_from_account_model->addCondition([['SchemeType',ACCOUNT_TYPE_BANK],['SchemeType',ACCOUNT_TYPE_SAVING]]);
		//$account_from_account_model->addCondition('branch_id',$this->app->current_branch->id);
		$account_from_account_model->add('Controller_Acl');

		$form->addField('Number','net_salary')->validateNotNull();
		$form->addField('autocomplete/Basic','submitted_into')->validateNotNull()->setModel($account_from_account_model);
		$form->addField('Number','tds');
		$form->addField('Number','pf');
		$form->addField('Number','esi');
		$form->addField('Number','health_insurance');
		$form->addField('Number','security');

		$form->addField('Text','narration');
		$form->addSubmit('Execute Salary Transaction');

		if($form->isSubmitted()){
			try {
				echo '<pre>';
				print_r(($form));
				exit;
				$this->api->db->beginTransaction();
				$dr = $this->add('Model_Account')->load($form['salary_account']);
				$dr_amount=0;
				
				$branch_n_devision = 0;
				if($dr['branch_code'] != "UDR"){
					$branch_n_devision = 1;
					$bnd_tran = $this->add('Model_Transaction');
					$narration = "Being Branch & Devision Deposited From ".$this->app->currentBranch['Code']." to ".$dr['branch_code']." For ".TRA_SALARY_AND_ALLOWENCES;
					$udr_branch = $this->add('Model_Branch')->loadBy('Code','UDR');
					$bnd_tran->createNewTransaction(BRANCH_AND_DIVISIONS,$udr_branch,$this->app->now,$narration,null,['reference_id'=>$dr->id]);
				} 

				$transaction = $this->add('Model_Transaction');
				$transaction->createNewTransaction(TRA_SALARY_AND_ALLOWENCES,$dr->ref('branch_id'),$this->app->now,$form['narration']);
				
				if($form['net_salary']){
					$transaction->addCreditAccount($this->add('Model_Account')->load($form['submitted_into']),$form['net_salary']);
					$dr_amount += $form['net_salary'];
				}

				// credit
				// jadhol udr branch and devision for jhd
				if($form['tds'] AND !$branch_n_devision){
					// fixed account udr tds
					$tds_account = "UDR".SP.BRANCH_TDS_ACCOUNT;
					$transaction->addCreditAccount($tds_account,$form['tds']);
					$dr_amount += $form['tds'];
				}

				if($form['pf'] ANd !$branch_n_devision){
					// fixed account udr pf
					$cr_account = "UDR".SP.'PF ACCOUNT';
					$transaction->addCreditAccount($cr_account,$form['pf']);
					$dr_amount += $form['pf'];
				}

				if($form['esi'] ANd !$branch_n_devision){
					// fixed account udr esi
					$cr_account = "UDR".SP.'ESI ACCOUNT';
					$transaction->addCreditAccount($cr_account,$form['esi']);
					$dr_amount += $form['pf'];
				}

				if($form['health_insurance'] AND !$branch_n_devision){
					// fixed account udr insurance
					$cr_account = "UDR".SP.'INSURANCE ON STAFF (STAR HEALTH)';
					$transaction->addCreditAccount($cr_account,$form['health_insurance']);
					$dr_amount += $form['health_insurance'];
				}

				if($branch_n_devision){
					$amount = (($form['tds']?$form['tds']:0) + ($form['pf']?$form['pf']:0) + ($form['esi']?$form['esi']:0) + ($form['health_insurance']?$form['health_insurance']:0));
					$cr_account = "UDR".SP.BRANCH_AND_DIVISIONS.SP."For".SP.$dr['branch_code'];
					$transaction->addCreditAccount($cr_account,$amount);
					$dr_amount += $amount;
				}

				if($form['security']){
					$transaction->addCreditAccount($dr['branch_code'].SP.'STAFF SECURITY',$form['security']);
					$dr_amount += $form['security'];
				}
				$transaction->addDebitAccount($dr,$dr_amount);
				$transaction->execute();
				
				// branch and devision entry
				// new entry udr
				// credit tds, pf and heath
				// debit jhd branch and devision for udr
				if($branch_n_devision){
					$bnd_amount = 0;

					if($form['tds']){
						$bnd_tran->addCreditAccount("UDR".SP.BRANCH_TDS_ACCOUNT,$form['tds']);
						$bnd_amount += $form['tds'];
					} 
					if($form['pf']){
						$bnd_tran->addCreditAccount("UDR".SP.'PF ACCOUNT',$form['pf']);
						$bnd_amount += $form['pf'];
					}
					if($form['esi']){
						$bnd_tran->addCreditAccount("UDR".SP.'ESI ACCOUNT',$form['esi']);
						$bnd_amount += $form['esi'];
					}  
					if($form['health_insurance']){
						$bnd_tran->addCreditAccount("UDR".SP.'INSURANCE ON STAFF (STAR HEALTH)',$form['health_insurance']);
						$bnd_amount += $form['health_insurance'];
					} 

					$bnd_tran->addDebitAccount($dr['branch_code'].SP.BRANCH_AND_DIVISIONS.SP.'FOR UDR',$bnd_amount);
					$bnd_tran->execute();
				}
				// end of branch and devidion entry

				$this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}

			$form->js(null,$form->js()->univ()->successMessage('Done'))->reload()->execute();
			
		}
	}
}