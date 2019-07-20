<?php

class page_reports_fcl extends Page {
	public $title = "FCL Report";

	function init() {
		parent::init();

		$account_id = $this->app->stickyGET('account');

		$account_model = $this->add('Model_Active_Account');

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic', 'account')
			->validateNotNull()
			->setModel($account_model);
		$form->addSubmit('Go');

		$section = $this->add('View');
		$view = $section->add('View', null, null, ['view/fcl']);

		$total_dr = 0;
		$total_cr = 0;
		if ($account_id) {

			$dealer_join = $account_model->leftJoin('dealers', 'dealer_id');
			$dealer_join->addField('loan_panelty_per_day');
			$branch_j = $account_model->join('branches', 'branch_id');
			$branch_j->join('closings.branch_id')
				->addField('daily');

			$account_model->addExpression('first_premium_date')->set($account_model->refSQL('Premium')->setLimit(1)->setOrder('DueDate')->fieldQuery('DueDate'));
			$account_model->addExpression('last_premium_date')->set($account_model->refSQL('Premium')->setLimit(1)->setOrder('DueDate', 'desc')->fieldQuery('DueDate'));
			$account_model->addExpression('current_month_premium_date')->set(
				$account_model->refSQL('Premium')->setLimit(1)
					->addCondition('DueDate', '>=', date("Y-m-01", strtotime($this->api->today)))
					->addCondition('DueDate', '<', $this->api->nextDate(date('Y-m-t', strtotime($this->api->today))))
					->setOrder('DueDate')
					->fieldQuery('DueDate')
			);

			$account_model->addExpression('interest_rate')->set($account_model->refSQL('scheme_id')->fieldQuery('Interest'));
			$account_model->addExpression('premium_count')->set($account_model->refSQL('Premium')->count());
			$account_model->addExpression('PaneltyCharged')->set($account_model->refSQL('Premium')->sum('PaneltyCharged'));
			$account_model->addExpression('uncounted_panelty_days')->set(function ($m, $q) {
				return $q->expr('DATEDIFF("[0]",[1]) - 1', array($m->api->today, $m->getElement('daily'))); //$account_model->refSQL('Premium')->sum('PaneltyCharged'));
			});

			$account_model->addExpression('AmountCreditedTotal')->set($account_model->refSQL('TransactionRow')->sum('amountCr'));
			$account_premium_ref_m = $account_model->refSQL('Premium');
			$account_model->addExpression('AmountCreditedEMI')->set($account_premium_ref_m->sum($account_model->dsql()->expr('[0]*[1]', [$account_premium_ref_m->getElement('Amount'), $account_premium_ref_m->getElement('Paid')])));
			$account_model->addExpression('AmountCreditedPenalty')->set($account_model->refSQL('TransactionRow')->addCondition('transaction_type', TRA_PENALTY_AMOUNT_RECEIVED)->sum('amountCr'));

			// intereset transaction se jitna byaj debit hua he , uski total old forclose report me calculation he
			$account_model->addExpression('monthly_interest')->set(function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', 'InterestPostingsInLoanAccounts');
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->addExpression('penalty_received')->set(function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')
					->addCondition('transaction_type', TRA_PENALTY_AMOUNT_RECEIVED);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountCr')]);
			});

			// TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT
			// deposite type ke transaction ki total
			$account_model->addExpression('total_deposite')->set(function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')
					->addCondition('transaction_type', TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountCr')]);
			});

			$model_memo_tran = $this->add('Model_Memorandum_Transaction');
			$type = $model_memo_tran->getTransactionType();
			foreach ($type as $memo_transaction_type => $caption) {
				$account_model->addExpression($memo_transaction_type)->set(function ($m, $q) use ($memo_transaction_type, $caption) {
					$mtr = $m->add('Model_Memorandum_TransactionRow');
					$mtr->addCondition('account_id', $m->getElement('id'))
						->addCondition('memo_transaction_type', $memo_transaction_type);
					return $q->expr('IFNULL([0],0)', [$mtr->sum('amountDr')]);
				});
			}

			$account_model->addExpression('without_gst_society_notice_sent', function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', TRA_SOCIETY_NOTICE_SENT);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->addExpression('without_gst_visit_charge', function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', TRA_VISIT_CHARGE);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->addExpression('without_gst_legal_notice_sent', function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', TRA_LEGAL_NOTICE_SENT);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});
			$account_model->addExpression('without_gst_godowncharge_debited', function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', TRA_GODOWNCHARGE_DEBITED);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->addExpression('without_gst_notice_sent_after_cheque_returned', function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', TRA_NOTICE_SENT_AFTER_CHEQUE_RETURNED);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->addExpression('without_gst_noc_handling_charge_received', function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', TRA_NOC_HANDLING_CHARGE_RECEIVED);
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->addExpression('without_gst_other_charge', function ($m, $q) {
				$tr = $m->refSQL('TransactionRow', 'model')
					->addCondition('transaction_type', '<>', array(
						TRA_PENALTY_AMOUNT_RECEIVED,
						TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT,
						TRA_SOCIETY_NOTICE_SENT,
						TRA_NOC_HANDLING_CHARGE_RECEIVED,
						TRA_NOTICE_SENT_AFTER_CHEQUE_RETURNED,
						TRA_GODOWNCHARGE_DEBITED,
						TRA_LEGAL_NOTICE_SENT,
						TRA_LEGAL_CHARGE_RECEIVED,
						TRA_VISIT_CHARGE,
						TRA_INTEREST_POSTING_IN_LOAN,
						TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT,
						TRA_LOAN_ACCOUNT_OPEN,
					));
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->load($account_id);
		} else {
			$account_model->tryLoad(-1);
		}

		if ($account_id) {
			// echo "<pre>";
			// print_r($account_model->data);
			// echo "</pre>";
			// old for close report me for_close_charge ki calculation
			$first_premium = new MyDateTime($account_model['first_premium_date']);
			$today_date = $this->api->today;
			$today = new MyDateTime(strtotime($today_date) < strtotime($account_model['last_premium_date']) ? $today_date : $account_model['last_premium_date']);
			$interval = $today->diff($first_premium);
			$month = $interval->m + ($interval->y * 12);
			$interests_for_months = $month + 1;
			$monthly_interest = round(($account_model['Amount'] * ($account_model['interest_rate'] / 100) / 12) * ($account_model['premium_count'] + 1) / $account_model['premium_count']);

			$total_dr += $pre_clouser_intereset = round(($account_model['premium_count'] - $interests_for_months) * ($monthly_interest * 40 / 100.00));
			$view->template->trySet('pre_clouser_interest', $pre_clouser_intereset);

			if (strtotime($account_model['last_premium_date'] . '+1 month') > strtotime($this->api->today)) {
				$time_over_charge = 0;
			} else {
				$bal = $account_model->getOpeningBalance($this->api->nextDate(date("Y-m-d", strtotime($account_model['last_premium_date'] . '+1 month'))));
				$days = $this->api->my_date_diff($this->api->today, date("Y-m-d", strtotime($account_model['last_premium_date'] . '+1 month')));
				$total_dr += $time_over_charge = round(($bal['Dr'] - $bal['Cr']) * ($account_model['interest_rate'] / 100) / 365 * $days['days_total']);
			}

			$total_dr += $account_model['Amount'] + $account_model['monthly_interest'];
			$total_dr += $account_model['nach_transaction_file_canceling_charge_received'] ?: 0;
			// total penelty
			$total_dr += $total_penalty = ($account_model['PaneltyCharged'] + $account_model['uncounted_panelty_days'] * $account_model['loan_panelty_per_day']);
			$total_dr += $total_society_notice_sent = ($account_model['without_gst_society_notice_sent'] + $account_model['society_notice_sent']);
			$total_dr += $total_visit_charge = ($account_model['visit_charge'] + $account_model['without_gst_visit_charge']);
			$total_dr += $total_legal_notice_sent = ($account_model['legal_notice_sent'] + $account_model['without_gst_legal_notice_sent']);
			$total_dr += $total_godowncharge_debited = ($account_model['godowncharge_debited'] + $account_model['without_gst_godowncharge_debited']);
			$total_dr += $total_notice_sent_after_cheque_returned = ($account_model['notice_sent_after_cheque_returned'] + $account_model['without_gst_notice_sent_after_cheque_returned']);
			$total_dr += $total_legal_expenses = $account_model['legal_expenses_received'];
			$total_dr += $total_noc_handling_charge_received = ($account_model['noc_handling_charge_received'] + $account_model['without_gst_noc_handling_charge_received']);
			$total_dr += $total_other_charge = ($account_model['without_gst_other_charge'] + $account_model['cheque_returned'] + $account_model['legal_notice_sent_for_bike_auction'] + $account_model['final_recovery_notice_sent'] + $account_model['insurance_processing_fees'] + $account_model['nach_registration_fees_charge_received'] + $account_model['file_cancel_charge']);

			$view->template->trySet('total_penalty', $total_penalty);
			$view->template->trySet('total_society_notice_sent', $total_society_notice_sent);
			$view->template->trySet('total_visit_charge', $total_visit_charge);
			$view->template->trySet('total_legal_notice_sent', $total_legal_notice_sent);
			$view->template->trySet('total_godowncharge_debited', $total_godowncharge_debited);
			$view->template->trySet('total_notice_sent_after_cheque_returned', $total_notice_sent_after_cheque_returned);
			$view->template->trySet('total_legal_expenses', $total_legal_expenses);
			$view->template->trySet('total_noc_handling_charge_received', $total_noc_handling_charge_received);
			$view->template->trySet('total_other_charge', $total_other_charge);
			$view->template->trySet('total_time_over_charge', $time_over_charge);

			$view->template->trySet('total_dr', $total_dr);
		}

		$view->setModel($account_model);

		if ($form->isSubmitted()) {
			$form->js(null, $view->js()->reload(['account' => $form['account']]))->execute();
		}

	}
}