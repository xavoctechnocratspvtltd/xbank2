<?php

class page_transactions_deposit extends Page {
	public $title ='Deposit Amount';
	function init(){
		parent::init();

		$form = $this->add('Form');
		$account_model=$this->add('Model_Account');
		// $account_model->addCondition('branch_id',$this->api->currentBranch->id);
		 // $model->addCondition(
   //          $model->dsql()->orExpr()
   //              ->where('transaction_type', TRA_ACCOUNT_OPEN_AGENT_COMMISSION)
   //              ->where('transaction_type', TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT)
   //      );



		$account_field = $form->addField('autocomplete/Basic',array('name'=>'account'))->validateNotNull();
		$account_model->filter(array($account_model->scheme_join->table_alias.'.SchemeGroup'=>array('%Bank Accounts%','%Suspence Account%','%Cash Account%','%Branch & Divisions%'),$account_model->table_alias.'.account_type'=>array('%Saving%','%Current%')));
		$account_field->setModel($account_model,'AccountNumber');

		// $account_field->model->debug();

		$form->addField('Number','amount')->validateNotNull();
		$form->addField('autocomplete/Basic','account_to_debit')->setFieldHint('sdfsd')->setModel('Account','AccountNumber');
		$form->addField('Text','narration');
		$form->addSubmit('Deposit');

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->loadBy('AccountNumber',$form['account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber',$form['account']);

			try {
				$this->api->db->beginTransaction();
			    $account_model->deposit($form['amount'],$form['narration'],$form['account_to_debit']?array(array($form['account_to_debit']=>$form['amount'])):array(),$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- deposited in " . $form['account'])->execute();
		}
	}
}