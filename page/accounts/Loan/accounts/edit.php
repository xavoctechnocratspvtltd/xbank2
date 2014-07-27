<?php

class page_accounts_Loan_accounts_edit extends Page {
	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTab('Change Member');
		$tab2=$tabs->addTab('Change Dealer');


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
			$loan_account->load($_GET['accounts_id']);

			$new_member_model=$this->add('Model_ActiveMember');
			$new_member_model->load($member_form['new_member']);

			$loan_account->changeMember($new_member_model);

			$member_form->js(null,$member_form->js()->univ()->_closeDialog())->reload()->execute();

		}

		$dealer_form=$tab2->add('Form');
		
		$dealer_account_field=$dealer_form->addField('autocomplete/Basic','account')->validateNotNull();
		$account_model = $this->add('Model_Account_Loan');
		$dealer_account_field->setModel($account_model);

		$dealer_field=$dealer_form->addField('autocomplete/Basic','new_dealer')->validateNotNull();
		$dealer_model=$this->add('Model_ActiveDealer');
		$dealer_field->setModel($dealer_model);

		$dealer_form->addSubmit('Change');

		if($dealer_form->isSubmitted()){
			$loan_account=$this->add('Model_Loan_Account');
			$loan_account->load($_GET['accounts_id']);

			$new_dealer_model=$this->add('Model_ActiveDealer');
			$new_dealer_model->load($dealer_form['new_dealer']);

			$loan_account->changeDealer($new_dealer_model);

			$dealer_form->js(null,$dealer_form->js()->univ()->_closeDialog())->reload()->execute();

		}

	}
}