<?php
class page_reports_member_loaninsurancenew extends Page {
	public $title = "Loan Member Insurance Report";

	function init() {
		parent::init();

		$filter = $this->app->stickyGET('filter');
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');

		$till_date = $till_date = $this->api->today;
		if ($_GET['to_date']) {
			$till_date = $_GET['to_date'];
		}

		$form = $this->add('Form');
		$form->addField('DatePicker', 'from_date');
		$form->addField('DatePicker', 'to_date');
		// $form->addField('dropdown','type')->setValueList(array_merge(array('0'=>'All','CC'=>'CC'),array_combine(explode(',',LOAN_TYPES),explode(',',LOAN_TYPES))));
		$form->addSubmit('GET List');

		$grid = $this->add('Grid_AccountsBase');
		$grid->add('H3', null, 'grid_buttons')->set('Loan Insurance List As On ' . date('d-M-Y', strtotime($till_date)));

		$accounts_model = $this->add('Model_Account');
		$m_join = $accounts_model->leftJoin('member_insurance.accounts_id', null, null, 'memberinsu');
		$m_join->addField('next_insurance_due_date');

		$accounts_model->addExpression('renew_date')->set(function ($m, $q) {
			return $q->expr('if([0],DATE([0]),DATE([1]))', [$m->getElement('next_insurance_due_date'), $m->getElement('created_at')]);
		})->caption('Applicable Renew Date');

		$accounts_model->addCondition(
			$accounts_model->dsql()->orExpr()
				->where('SchemeType', 'Loan')
			// ->where('SchemeType','CC')
		);

		if ($_GET['filter']) {
			$this->api->stickyGET('filter');
			$accounts_model->addCondition('renew_date', '>=', $from_date);
			$accounts_model->addCondition('renew_date', '<', $to_date);

			// $accounts_model->addCondition(
			// 	$accounts_model->dsql()->orExpr()
			// 		->where(
			// 				$accounts_model->dsql()->andExpr()
			// 					->where($accounts_model->getElement('next_insurance_due_date'),'>=',$from_date)
			// 					->where($accounts_model->getElement('next_insurance_due_date'),'<',$to_date)
			// 		)->where(
			// 				$accounts_model->dsql()->andExpr()
			// 					->where($accounts_model->getElement('created_at'),'>=',$from_date)
			// 					->where($accounts_model->getElement('created_at'),'<',$to_date)
			// 		)
			// );

		} else {
			$accounts_model->addCondition('id', -1);
		}

		$accounts_model->addExpression('member_name')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('name');
		});

		$accounts_model->addExpression('DOB')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('DOB');
		});

		$accounts_model->addExpression('gender')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('gender');
		});

		$accounts_model->addExpression('father_name')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('FatherName');
		});

		$accounts_model->addExpression('address')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('PermanentAddress');
		});

		$accounts_model->addExpression('age')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('DOB');
		});

		$accounts_model->addExpression('nominee')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('Nominee');
		});

		$accounts_model->addExpression('relation_with_nominee')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('RelationWithNominee');
		});

		$accounts_model->addExpression('phone_nos')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('PhoneNos');
		});

		$accounts_model->getElement('CurrentBalanceDr')->caption('Current Balance');
		$grid->setModel($accounts_model, array('AccountNumber', 'created_at', 'renew_date', 'scheme', 'gender', 'member_name', 'father_name', 'address', 'phone_nos', 'DOB', 'age', 'nominee', 'relation_with_nominee', 'Amount'));
		$self = $this;
		$grid->addColumn('current_balance');
		$grid->addMethod('format_current_balance', function ($g, $f) use ($self) {
			// throw new \Exception($g->model->id, 1);
			$acc_model = $self->add('Model_Account')->load($g->model->id);
			$opening_array = $acc_model->getOpeningBalance($self->api->nextDate($self->api->today));
			$opening_array['DR'] - $opening_array['CR'];
			if (($opening_array['DR'] - $opening_array['CR']) > 0) {
				$opening_amount = $opening_array['DR'] - $opening_array['CR'] . " DR";
			} else {
				$opening_amount = $opening_array['CR'] - $opening_array['DR'] . " CR";
			}
			$g->current_row[$f] = $opening_amount;
		});

		$grid->addFormatter('current_balance', 'current_balance');

		$grid->addMethod('format_age', function ($g, $f) {
			$age = array();
			if ($g->current_row[$f] != '0000-00-00 00:00:00') {
				$age = $g->api->my_date_diff($g->api->today, $g->current_row[$f] ?: $g->api->today);
			}
			$g->current_row[$f] = $g->current_row[$f] ? $age['years'] : "";
		});

		$grid->addFormatter('age', 'age');
		$grid->addColumn('text', 'insurance_amount');

		$paginator = $grid->addPaginator(500);
		$grid->skip_var = $paginator->skip_var;

		$grid->addSno();
		$grid->removeColumn('scheme');

		//Formatter for Nominee

		$grid->addMethod('format_nominee', function ($g, $q) {
			if ($g->model['nominee']) {
				$nominee = $g->model['nominee'];
			} else {
				$sm = $g->add('Model_Account_SM')->addCondition('member_id', $g->model['member_id']);
				$sm->tryLoadAny();
				$nominee = $sm['Nominee'];
			}
			$g->current_row_html['nominee'] = $nominee;
		});

		$grid->addFormatter('nominee', 'nominee');

		$grid->addMethod('format_relation_with_nominee', function ($g, $q) {
			if ($g->model['relation_with_nominee']) {
				$nominee = $g->model['relation_with_nominee'];
			} else {
				$sm = $g->add('Model_Account_SM')->addCondition('member_id', $g->model['member_id']);
				$sm->tryLoadAny();
				$nominee = $sm['RelationWithNominee'];
			}
			$g->current_row_html['relation_with_nominee'] = $nominee;
		});
		$grid->addFormatter('relation_with_nominee', 'relation_with_nominee');

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if ($form->isSubmitted()) {
			$send = array('from_date' => $form['from_date'] ?: 0, 'to_date' => $form['to_date'] ?: 0, 'filter' => 1);
			$grid->js()->reload($send)->execute();
		}

	}
}