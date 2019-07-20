<?php

class page_transactions_recoveryandlegaltransactions extends Page {

	public $title = 'Recovery And Legal Transaction';

	function init() {
		parent::init();

		$this->add('Controller_Acl');

		$transaction_array = RECOEVRY_ANY_LEGAL_CHARGES_ACCOUNT_TRA_ARRAY;

		$account_from_account_model = $this->add('Model_Active_Account', array('table_alias' => 'acc'));
		$account_from_account_model->addCondition('SchemeType', '<>', ACCOUNT_TYPE_RECURRING);
		$account_from_account_model->addCondition('SchemeType', '<>', ACCOUNT_TYPE_DDS);
		$account_from_account_model->addCondition('SchemeType', '<>', ACCOUNT_TYPE_FIXED);

		$account_from_account_model->add('Controller_Acl');

		$form = $this->add('Form');
		$form->addField('DropDown', 'type')->setEmptyText('Please Select')->validateNotNull()->setValueList(array_combine(array_keys($transaction_array), array_keys($transaction_array)));
		$form->addField('Number', 'amount')->validateNotNull();
		$amount_from_account_field = $form->addField('autocomplete/Basic', 'amount_from_account')->validateNotNull();
		$hint_view = $amount_from_account_field->other_field->belowField()->add('View');

		$amount_from_account_field->setModel($account_from_account_model, 'AccountNumber');

		if ($_GET['check_cr']) {
			$acc_bal_temp = $this->add('Model_Account');
			$acc_bal_temp->tryLoadBy('AccountNumber', $_GET['AccountNumber']);

			if ($acc_bal_temp->loaded()) {
				$bal = $acc_bal_temp->getOpeningBalance();
				$bal_cr = $bal['Dr'] - $bal['Cr'];
				if ($bal_cr < 0) {
					$amount_field_view->set('Already Cr Balance');
				}

			}
		}

		$amount_from_account_field->js('change', $hint_view->js()->reload(array('check_cr' => 1, 'AccountNumber' => $amount_from_account_field->js()->val())));

		$form->addField('Text', 'narration');
		$form->addSubmit('Do Transaction');

		if ($form->isSubmitted()) {

			$account_model_temp = $this->add('Model_Account')
				->loadBy('AccountNumber', $form['amount_from_account']);

			if (!$account_model_temp->loaded()) {
				$form->displayError('amount', 'Oops');
			}

			$account_model = $this->add('Model_Account_' . $account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber', $form['amount_from_account']);

			// Visit charge condition, must not be withing 15 days of same month based on account model visit_done_on field
			$last_visit_in_same_month = (date('m', strtotime($account_model['visit_done_on'])) == date('m', strtotime($this->app->now)));
			$last_visit_within_last_15_days = ($this->app->my_date_diff($account_model['visit_done_on'], $this->app->today)['days_total'] < 15);
			$is_visit_transaction_type = ($form['type'] == 'visit_done');

			if ($is_visit_transaction_type && $last_visit_in_same_month && $last_visit_within_last_15_days) {
				$form->displayError('type', 'Visit must not be within 15 days in same month');
			}

			try {
				$this->api->db->beginTransaction();

				$account_dr = $this->add('Model_Account')
					->loadBy('AccountNumber', $form['amount_from_account']);
				$account_cr = $this->add('Model_Account')
					->loadBy('AccountNumber', $this->api->currentBranch['Code'] . SP . $transaction_array[$form['type']][0]);
				$transaction = $this->add('Model_Transaction');
				// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
				$transaction->createNewTransaction($transaction_array[$form['type']][1], $this->api->currentBranch, $this->app->now, $form['narration'], null, ['reference_id' => $account_dr->id]);

				$transaction->addDebitAccount($account_dr, $form['amount']);
				$transaction->addCreditAccount($account_cr, $form['amount']);

				$transaction->execute();

				$account_dr['is_' . $form['type']] = true;
				$account_dr[$form['type'] . '_on'] = $this->app->now;
				$account_dr->save();

				// throw new \Exception("Error Processing Request", 1);

				$this->api->db->commit();
			} catch (Exception $e) {
				$this->api->db->rollBack();
				throw $e;
			}
			$form->js(null, $form->js()->reload())->univ()->successMessage($form['amount'] . "/- Vist Charge added in " . $form['amount_from_account'])->execute();
		}
	}
}