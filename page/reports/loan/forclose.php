<?php

class page_reports_loan_forclose extends Page {
	public $title = "For Close Repots";
	function page_index() {
		// parent::init();

		$form = $this->add('Form');
		$loan_accoun = $form->addField('autocomplete/Basic', 'account_no')->validateNotNull();
		$loan_accoun->setModel('Account_Loan');

		$form->addSubmit('GET List');

		$grid = $this->add('Grid_AccountsBase');
		$grid->add('H3', null, 'grid_buttons')->set('For Close Report');

		$account_model = $this->add('Model_Account_Loan');

		if ($_GET['filter']) {
			if ($_GET['account_no']) {
				$account_no = $this->api->stickyGET('account_no');
			}

		} else {
			$account_no = -1;
		}

		$account_model->addCondition('id', $account_no);
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

		$grid->addSno();
		$grid->setModel($account_model, array('AccountNumber', 'member', 'Amount', 'dealer', 'first_premium_date', 'last_premium_date', 'current_month_premium_date', 'interest_rate', 'premium_count', 'daily', 'uncounted_panelty_days', 'loan_panelty_per_day', 'PaneltyCharged', 'AmountCreditedEMI', 'AmountCreditedPenalty', 'AmountCreditedOther', 'AmountCreditedTotal'));

		$grid->addMethod('format_total_panalty', function ($g, $f) {
			$g->current_row[$f] = $g->model['PaneltyCharged'] + $g->model['uncounted_panelty_days'] * $g->model['loan_panelty_per_day'];
		});

		$grid->addMethod('format_monthly_interest_months', function ($g, $f) {
		});

		$grid->addMethod('format_AmountCreditedOther', function ($g, $f) {
			$g->current_row[$f] = $g->model['AmountCreditedTotal'] - ($g->model['AmountCreditedEMI'] + $g->model['AmountCreditedPenalty']);
		});

		$grid->addMethod('format_monthly_interest', function ($g, $f) {
			$first_premium = new MyDateTime($g->model['first_premium_date']);
			$today_date = $g->api->today;
			$today = new MyDateTime(
				strtotime($today_date) < strtotime($g->model['last_premium_date']) ?
				$today_date : $g->model['last_premium_date']
			);
			$interval = $today->diff($first_premium);
			$month = $interval->m + ($interval->y * 12);
			$months = $month + 1;

			$g->interests_for_months = $months;

			$g->monthly_interest = round(($g->model['Amount'] * ($g->model['interest_rate'] / 100) / 12) * ($g->model['premium_count'] + 1) / $g->model['premium_count']);
			$g->current_row[$f] = $g->monthly_interest * $g->interests_for_months;
		});

		$grid->addMethod('format_for_close_charge', function ($g, $f) {
			$g->forclose_charges = round(($g->model['premium_count'] - $g->interests_for_months) * ($g->monthly_interest * 40 / 100.00));
			$g->current_row[$f] = $g->forclose_charges;
		});

		$grid->addMethod('format_visit_charge', function ($g, $f) {
			$g->current_row[$f] = $g->model->ref('TransactionRow', 'model')->addCondition('transaction_type', TRA_VISIT_CHARGE)->sum('amountDr')->getOne();
		});

		$grid->addMethod('format_legal_charge', function ($g, $f) {
			$g->current_row[$f] = $g->model->ref('TransactionRow', 'model')->addCondition('transaction_type', TRA_LEGAL_CHARGE_RECEIVED)->sum('amountDr')->getOne();
		});

		$grid->addMethod('format_other_charge', function ($g, $f) {
			$g->current_row[$f] = $g->model->ref('TransactionRow', 'model')->addCondition('transaction_type', '<>', array(TRA_LEGAL_CHARGE_RECEIVED, TRA_VISIT_CHARGE, TRA_INTEREST_POSTING_IN_LOAN, TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT, TRA_LOAN_ACCOUNT_OPEN))->sum('amountDr')->getOne();
		});

		$grid->addMethod('format_time_over_charge', function ($g, $f) {
			if (strtotime($g->model['last_premium_date'] . '+1 month') > strtotime($g->api->today)) {
				$g->time_over_charge = 0;
				$g->current_row[$f] = 0;
				return;
			}

			$bal = $g->model->getOpeningBalance($g->api->nextDate(date("Y-m-d", strtotime($g->model['last_premium_date'] . '+1 month'))));

			$days = $g->api->my_date_diff($g->api->today, date("Y-m-d", strtotime($g->model['last_premium_date'] . '+1 month')));
			$g->time_over_charge = round(($bal['Dr'] - $bal['Cr']) * ($g->model['interest_rate'] / 100) / 365 * $days['days_total']);
			$g->current_row[$f] = $g->time_over_charge;
		});

		$grid->addMethod('format_for_close_amount', function ($g, $f) {
			$c = $g->current_row;
			$g->current_row[$f] = $g->model['Amount'] + $c['monthly_interest'] + $c['total_panalty'] + $c['for_close_charge'] + $c['visit_charge'] + $c['legal_charge'] + $c['other_charge'] + $c['time_over_charge'] - $g->model['AmountCreditedTotal'];
		});

		$grid->addColumn('total_panalty', 'total_panalty');
		$grid->addColumn('monthly_interest_months', 'monthly_interest_months');
		$grid->addColumn('monthly_interest', 'monthly_interest');
		$grid->addColumn('visit_charge', 'visit_charge');
		$grid->addColumn('legal_charge', 'legal_charge');
		$grid->addColumn('other_charge', 'other_charge');
		$grid->addColumn('for_close_charge', 'for_close_charge');
		$grid->addColumn('time_over_charge', 'time_over_charge');
		$grid->addColumn('for_close_amount', 'for_close_amount');
		$grid->addColumn('AmountCreditedOther', 'AmountCreditedOther');

		$grid->addOrder()
			->move('PaneltyCharged', 'before', 'total_panalty')
			->move('AmountCreditedEMI', 'after', 'time_over_charge')
			->move('AmountCreditedPenalty', 'after', 'AmountCreditedEMI')
			->move('AmountCreditedOther', 'after', 'AmountCreditedPenalty')
			->move('AmountCreditedTotal', 'after', 'AmountCreditedOther')
			->now();

		$grid->removeColumn('current_month_premium_date');
		$grid->removeColumn('premium_count');
		$grid->removeColumn('daily');
		$grid->removeColumn('daily');
		$grid->removeColumn('uncounted_panelty_days');
		$grid->removeColumn('loan_panelty_per_day');
		$grid->removeColumn('PaneltyCharged');
		$grid->removeColumn('monthly_interest_months');

		$grid->addFormatter('member', 'wrap');

		$grid->addPaginator(5);

		if ($form->isSubmitted()) {
			$grid->js()->reload(array('account_no' => $form['account_no'], 'filter' => 1))->execute();
		}

	}

}
