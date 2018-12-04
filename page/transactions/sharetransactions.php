<?php

class page_transactions_sharetransactions extends Page {
	public $title = "Share Transactions";

	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$tabs = $this->add('Tabs');
		$tr_tab = $tabs->addTab('Share Transfer');
		$bb_tab = $tabs->addTab('Share Buy Back');

		$this->manage_Transfer($tr_tab);
		$this->manage_buyBack($bb_tab);
	}

	function manage_Transfer($tab){
		$form = $tab->add('Form');

		$from_field = $form->addField('autocomplete/Basic','from_account')->validateNotNull();
		$to_field = $form->addField('autocomplete/Basic','to_account')->validateNotNull();

		$form->addField('Text','shares')->validateNotNull()->setFieldHint('Comma seperated share numbers');
		$form->addField('Text','submitted_certificates')->validateNotNull()->setFieldHint('Comma seperated share certificate numbers');
		$form->addSubmit('Transfer');

		$sm_accounts = $this->add('Model_Account_SM')->addCondition('ActiveStatus',true);
		$from_field->setModel($sm_accounts);
		$to_field->setModel($sm_accounts);

		if($form->isSubmitted()){
			$shares = array_map('trim', explode(",", $form['shares']));
			$submitted_certificates = array_map('trim', explode(",", $form['submitted_certificates']));
			$share_amount = count($shares) * RATE_PER_SHARE ;
			
			$from_account = $this->add('Model_Account_SM')->load($form['from_account']);
			$bal = $from_account->getOpeningBalance($this->app->nextDate());

			$cr_bal = $bal['Cr']- $bal['Dr'];
			if($cr_bal < $share_amount ){
				$form->displayError('from_account','Insufficient Cr Balance :' . $cr_bal. ' Required '. $share_amount);
			}

			if($cr_bal == $share_amount ){
				$form->displayError('from_account','Cannot transfer all shares, have to keep one minimum');
			}

			// TRA_SHARE_TRANSFER
			$share_m = $this->add('Model_Share');
			$ownership_check = $share_m->hasOwnership($shares,$from_account['member_id']);
			if($ownership_check !== true) {
				$form->displayError('shares','Following Shares are not owned by '. $from_account['member'].' => '. implode(", ", $ownership_check));
			}

			$actual_certifictes = $share_m->getCertificates($shares);
			if($actual_certifictes != $submitted_certificates ){
				$form->displayError('submitted_certificates','Required Certificates Nos are => '. implode(", ", $actual_certifictes));
			}
			

			$to_account = $this->add('Model_Account_SM')->load($form['to_account']);
			$share_m->transfer($from_account, $to_account, $shares, $submitted_certificates);

			$form->js(null, $form->js()->univ()->successMessage('Share Transered'))->reload()->execute();

		}

	}

	function manage_buyBack($tab){
		$form = $tab->add('Form');

		$from_field = $form->addField('autocomplete/Basic','from_account')->validateNotNull();
		$to_field = $form->addField('autocomplete/Basic','to_account')->validateNotNull();

		$form->addField('Text','shares')->validateNotNull()->setFieldHint('Comma seperated share numbers');
		$form->addField('Text','submitted_certificates')->validateNotNull()->setFieldHint('Comma seperated share certificate numbers');
		$form->addSubmit('Buy Back');

		$sm_accounts = $this->add('Model_Account_SM')->addCondition('ActiveStatus',true);
		$from_field->setModel($sm_accounts);

		$buyback_accounts = $this->add('Model_Account');
		$buyback_accounts->addCondition('AccountNumber',$this->api->current_branch['Code'] .SP . CASH_ACCOUNT);

		$to_field->setModel($buyback_accounts);

		if($form->isSubmitted()){
			$shares = array_map('trim', explode(",", $form['shares']));
			$submitted_certificates = array_map('trim', explode(",", $form['submitted_certificates']));
			$share_amount = count($shares) * RATE_PER_SHARE ;
			
			$from_account = $this->add('Model_Account_SM')->load($form['from_account']);
			$bal = $from_account->getOpeningBalance($this->app->nextDate());

			$cr_bal = $bal['Cr']- $bal['Dr'];
			if($cr_bal < $share_amount ){
				$form->displayError('from_account','Insufficient Cr Balance :' . $cr_bal. ' Required '. $share_amount);
			}

			if($cr_bal == $share_amount ){
				$form->displayError('from_account','Cannot transfer all shares, have to keep one minimum');
			}

			// TRA_SHARE_TRANSFER
			$share_m = $this->add('Model_Share');
			$ownership_check = $share_m->hasOwnership($shares,$from_account['member_id']);
			if($ownership_check !== true) {
				$form->displayError('shares','Following Shares are not owned by '. $from_account['member'].' => '. implode(", ", $ownership_check));
			}

			$actual_certifictes = $share_m->getCertificates($shares);
			if($actual_certifictes != $submitted_certificates ){
				$form->displayError('submitted_certificates','Required Certificates Nos are => '. implode(", ", $actual_certifictes));
			}

			$to_account = $this->add('Model_Account')->load($form['to_account']);
			$share_m->buyBack($from_account, $to_account, $shares, $submitted_certificates);

			$form->js(null, $form->js()->univ()->successMessage('Share Buy Back Complete'))->reload()->execute();

		}
	}
}