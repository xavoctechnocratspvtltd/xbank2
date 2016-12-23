<?php

class page_transactions_legalchargepaid extends Page {
	public $title ='Legal Charge Paid';
	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$account_from_account_model = $this->add('Model_Active_Account',array('table_alias'=>'acc'));
		$account_from_account_model->addCondition(
			$account_from_account_model->dsql()->orExpr()
				->where('_s.SchemeType',ACCOUNT_TYPE_SAVING)
				->where('(_s.name = "'.CASH_ACCOUNT_SCHEME.'" and acc.branch_id= '.$this->api->current_branch->id.' )')
				->where('(_s.name = "'.BANK_ACCOUNTS_SCHEME.'" and acc.branch_id= '.$this->api->current_branch->id.' )')
			);

		$form = $this->add('Form');
		
		$form->addField('Number','amount')->validateNotNull();
		$form->addField('autocomplete/Basic','amount_to_account')->setModel($account_from_account_model,'AccountNumber');
		$form->addField('Text','narration');
		$form->addSubmit('Legal Charge Paid');

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->loadBy('AccountNumber',$form['amount_to_account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber',$form['amount_to_account']);

			try {
				$this->api->db->beginTransaction();
			    $account_model->legalChargePaid($form['amount'],$form['narration'],$form['amount_to_account'],$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- Legal Charge Paid in " . $form['amount_to_account'])->execute();
		}
	}
}