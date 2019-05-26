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

			$account_model->addExpression('monthly_interest')->set(function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', 'InterestPostingsInLoanAccounts');
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
			});

			$account_model->addExpression('penalty')->set(function ($m, $q) {
				$tr = $m->refSQL('TransactionRow')->addCondition('transaction_type', 'PenaltyAccountAmountDeposit');
				return $q->expr('IFNULL([0],0)', [$tr->sum('amountDr')]);
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

			$account_model->load($account_id);
		} else {
			$account_model->tryLoad(-1);
		}

		if ($account_id) {
			// echo "<pre>";
			// print_r($account_model->data);
			// echo "</pre>";
			$total_dr += $account_model['Amount'] + $account_model['monthly_interest'] + $account_model['penalty'];
			foreach ($model_memo_tran->getTransactionType() as $field_name) {
				$total_dr += $account_model[$field_name];
			}

			$view->template->trySet('total_dr', $total_dr);
		}

		$view->setModel($account_model);

		if ($form->isSubmitted()) {
			$form->js(null, $view->js()->reload(['account' => $form['account']]))->execute();
		}

	}
}