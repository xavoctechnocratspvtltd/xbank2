<?php

class page_operations_edit extends Page {
	public $title='Admin Edit Operations';

	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTab('Change Member');
		$tab2=$tabs->addTab('Change Dealer');
		$tab3=$tabs->addTab('Change Agent');


		// ================ Member Change
		$member_form=$tab1->add('Form');
		$member_account_field=$member_form->addField('autocomplete/Basic','account')->validateNotNull();
		$account_model = $this->add('Model_Account_Loan');
		$member_account_field->setModel($account_model);
		
		$member_field=$member_form->addField('autocomplete/Basic','new_member')->validateNotNull();
		$member_model=$this->add('Model_ActiveMember');
		$member_field->setModel($member_model);

		$member_form->addSubmit('Change');

		if($member_form->isSubmitted()){
			$loan_account=$this->add('Model_Account_Loan');
			$loan_account->load($member_form['account']);

			$new_member_model=$this->add('Model_ActiveMember');
			$new_member_model->load($member_form['new_member']);

			try {
				$this->api->db->beginTransaction();
			    $loan_account->changeMember($new_member_model);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}

			$member_form->js()->reload()->execute();

		}

		// ============ DEALER
		$dealer_form=$tab2->add('Form');
		
		$dealer_account_field=$dealer_form->addField('autocomplete/Basic','account')->validateNotNull();
		$account_model = $this->add('Model_Account_Loan');
		$dealer_account_field->setModel($account_model);

		$dealer_field=$dealer_form->addField('autocomplete/Basic','new_dealer')->validateNotNull();
		$dealer_model=$this->add('Model_ActiveDealer');
		$dealer_field->setModel($dealer_model);

		$dealer_form->addSubmit('Change');

		if($dealer_form->isSubmitted()){
			$loan_account=$this->add('Model_Account_Loan');
			$loan_account->load($dealer_form['account']);

			$new_dealer_model=$this->add('Model_ActiveDealer');
			$new_dealer_model->load($dealer_form['new_dealer']);

			try {
				$this->api->db->beginTransaction();
			    $loan_account->changeDealer($new_dealer_model);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}

			$dealer_form->js()->reload()->execute();

		}

		// ============ Agent Change


		$agent_form=$tab3->add('Form');
		
		$agent_account_field=$agent_form->addField('autocomplete/Basic','account')->validateNotNull();
		$account_model = $this->add('Model_Account_Loan');
		$agent_account_field->setModel($account_model);

		$agent_field=$agent_form->addField('autocomplete/Basic','new_agent')->validateNotNull();
		$agent_model=$this->add('Model_Agent');
		$agent_model->addCondition('ActiveStatus',true);
		$agent_field->setModel($agent_model);

		$agent_form->addSubmit('Change');

		if($agent_form->isSubmitted()){
			$loan_account=$this->add('Model_Account_Loan');
			$loan_account->load($agent_form['account']);

			$new_agent_model=$this->add('Model_Agent');
			$new_agent_model->addCondition('ActiveStatus',true);
			$new_agent_model->load($agent_form['new_agent']);

			try {
				$this->api->db->beginTransaction();
			    $loan_account->changeAgent($new_agent_model);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}

			$agent_form->js()->reload()->execute();

		}

		
	
	}
}