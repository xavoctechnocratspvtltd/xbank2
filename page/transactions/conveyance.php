<?php

class page_transactions_conveyance extends Page {
	public $title ='Conveynace Transaction';
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

		// $account_model->addCondition(
		// 	$account_model->dsql()->orExpr()
		// 		->where('branch_id',$this->api->current_branch->id)
		// 		->where('DefaultAC',false)
		// 	);

		// $account_from_account_model->add('Controller_Acl');

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic',array('name'=>'staff'))->validateNotNull()->setModel('Employee')->addCondition('is_active',true);
			$form->addField('Number','amount')->validateNotNull();
		$amtfrmac = $form->addField('autocomplete/Basic','amount_from_account');
		$amtfrmac->setModel($account_from_account_model);
		$amtfrmac->set($this->api->current_branch->getCashAccount()->get('id'));
		
		$form->addField('Text','narration');
		$form->addSubmit('Conveynace');

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->load($form['amount_from_account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->load($form['amount_from_account']);

			try {
				$this->api->db->beginTransaction();
			    $account_model->conveyance($form['staff'], $form['amount'],$form['narration'],$form['amount_from_account'],$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- conveynace added in " . $form['amount_from_account'])->execute();
		}
	}
}