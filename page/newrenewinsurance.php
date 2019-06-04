<?php

class page_newrenewinsurance extends Page {
	public $title = " ";
	function init() {
		parent::init();

		// $this->add('Controller_Acl',['default_view'=>false]);

		$filter = $this->app->stickyGET('filter');
		$loan_type = $this->app->stickyGET('loan_type');
		$this->from_date = $from_date = $this->app->stickyGET('from_date');
		$this->to_date = $to_date = $this->app->stickyGET('to_date');
		if ($to_date) {
			$this->to_date = $to_date = $this->app->nextDate($to_date);
		}

		$col = $this->add('Columns');
		$col1 = $col->addColumn(4);
		$col3 = $col->addColumn(2);
		$col3->add('View')->setHtml('<div style="margin:0 auto;width:2px;height:100px;border:1px solid #f3f3f3;"></div>');
		$col2 = $col->addColumn(4);

		$col1->add('View_Info')->setElement('h3')->set('Filter Form');
		$form = $col1->add('Form');
		$form->addField('DatePicker', 'from_date')->validateNotNull();
		$form->addField('DatePicker', 'to_date')->validateNotNull();
		$form->addField('dropdown', 'loan_type')->setValueList(array('all' => 'All', 'vl' => 'VL', 'pl' => 'PL', 'fvl' => 'FVL', 'sl' => 'SL', 'hl' => 'HL', 'other' => 'Other'));
		$form->addSubmit('Get Record');

		$view = $this->add('View');
		$grid = $view->add('Grid');

		$col2->add('View_Success')->setElement('h3')->set('Update All Account Insurance');
		$ins_form = $col2->add('Form');
		$field_accounts = $ins_form->addField('Text', 'accounts')->setAttr('style', 'display:none;');
		$ins_form->addField('DatePicker', 'insurance_date')->validateNotNull();
		$ins_form->addField('DropDown', 'insurance_duration')
			->setValueList(['1' => '1 Year', '2' => '2 Year', '3' => '3 Year', '4' => '4 Year', '5' => '5 Year', '6' => '6 Year', '7' => '7 Year', '8' => '8 Year', '9' => '9 Year', '10' => '10 Year'])
			->setEmptyText('Please Select')
			->validateNotNull();
		$ins_form->addField('text', 'narration');
		$ins_form->addSubmit('Add/ Renew Insurance');

		$model = $view->add('Model_Account');
		$m_join = $model->leftJoin('member_insurance.accounts_id', null, null, 'memberinsu');
		$m_join->addField('next_insurance_due_date');
		$m_join->addField('insurance_number', 'name');
		$m_join->addField('insurance_record_id', 'id');
		$m_join->addField('is_renew');

		$model->addExpression('renew_date')->set(function ($m, $q) {
			return $q->expr('if([0],DATE([0]),DATE([1]))', [$m->getElement('next_insurance_due_date'), $m->getElement('created_at')]);
		})->caption('Applicable Renew Date');
		$model->addExpression('member_name')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('name');
		});

		$model->addExpression('DOB')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('DOB');
		});

		$model->addExpression('gender')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('gender');
		});

		$model->addExpression('father_name')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('FatherName');
		});

		$model->addExpression('address')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('PermanentAddress');
		});

		$model->addExpression('age')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('DOB');
		});

		$model->addExpression('nominee')->set(function ($m, $q) {
			$sm = $m->add('Model_Account_SM', ['table_alias' => 'sm'])->addCondition('member_id', $m->getElement('member_id'));
			$sm->setLimit(1);
			return $sm->fieldQuery('Nominee');
		});

		$model->addExpression('relation_with_nominee')->set(function ($m, $q) {
			$sm = $m->add('Model_Account_SM', ['table_alias' => 'smss'])->addCondition('member_id', $m->getElement('member_id'));
			$sm->setLimit(1);
			return $sm->fieldQuery('RelationWithNominee');
		});

		$model->addExpression('phone_nos')->set(function ($m, $q) {
			return $m->refSQL('member_id')->fieldQuery('PhoneNos');
		});

		$model->addExpression('current_balance')->set(function ($m, $q) {
			return $q->expr('IFNULL([0],0)-IFNULL([1],0)', [$m->getElement('CurrentBalanceDr'), $m->getElement('CurrentBalanceCr')]);
		})->sortable(true);

		$model->addCondition([['is_renew', false], ['is_renew', null]]);
		$model->addCondition('ActiveStatus', true);
		$model->addCondition($model->dsql()->orExpr()->where('SchemeType', 'Loan'));

		if ($filter) {
			$model->addCondition('renew_date', '>=', $from_date);
			$model->addCondition('renew_date', '<', $to_date);

			// $model->addCondition(
			// 	$model->dsql()->orExpr()
			// 		->where(
			// 				$model->dsql()->andExpr()
			// 					->where($model->getElement('next_insurance_due_date'),'>=',$from_date)
			// 					->where($model->getElement('next_insurance_due_date'),'<',$to_date)
			// 		)->where(
			// 				$model->dsql()->andExpr()
			// 					->where($model->getElement('created_at'),'>=',$from_date)
			// 					->where($model->getElement('created_at'),'<',$to_date)
			// 		)
			// );
			switch ($this->app->stickyGET('loan_type')) {
			case 'vl':
				$model->addCondition('AccountNumber', 'like', '%vl%');
				$model->addCondition('AccountNumber', 'not like', '%fvl%');
				break;
			case 'pl':
				$model->addCondition('AccountNumber', 'like', '%pl%');
				break;
			case 'fvl':
				$model->addCondition('AccountNumber', 'like', '%FVL%');
				break;
			case 'sl':
				$model->addCondition('AccountNumber', 'like', '%SL%');
				$model->addCondition('AccountNumber', 'not like', '%VL%');
				break;
			case 'hl':
				$model->addCondition('AccountNumber', 'like', '%HL%');
				break;
			case 'other':
				$model->addCondition('AccountNumber', 'not like', '%pl%');
				$model->addCondition('AccountNumber', 'not like', '%vl%');
				$model->addCondition('AccountNumber', 'not like', '%hl%');
				break;
			}
		} else {
			$model->addCondition("id", -1);
		}

		// $grid->addSelectable($field_accounts);
		// $grid->addSno();

		$grid->setModel($model, ['AccountNumber', 'created_at', 'gender', 'member_name', 'father_name', 'address', 'phone_nos', 'DOB', 'age', 'nominee', 'relation_with_nominee', 'current_balance', 'next_insurance_due_date', 'insurance_number', 'insurance_record_id', 'renew_date']);

		if ($form->isSubmitted()) {
			$view->js()->reload(['filter' => 1, 'from_date' => $form['from_date'], 'to_date' => $form['to_date'], 'loan_type' => $form['loan_type']])->execute();
		}

		if ($ins_form->isSubmitted()) {
			// generate sql query and execute with single command
			// member_id, accounts_id, name, insurance_start_date, insurance_duration, narration, next_insurance_due_date
			$account_array = json_decode($ins_form['accounts']);

			if (!count($account_array)) {
				throw new \Exception("please select at least one account to procced");
			}

			$query = $this->getQueryString($account_array, $ins_form);

			try {
				$this->api->db->beginTransaction();
				$this->app->db->dsql()->expr($query)->execute();
				$this->api->db->commit();
			} catch (Exception $e) {
				$this->api->db->rollback();
				throw $e;
			}

			$js_event = [
				$ins_form->js()->reload(),
				$view->js()->reload(['filter' => 0]),
			];

			$ins_form->js(null, $js_event)->univ()->successMessage('saved successfully')->execute();
		}

		// manually create a grid selectable
		// for collecting both Account_id AND Memberinsurance_id

		$grid->addMethod('format_select', function ($g, $f) {
			$id = $g->model->id . '_' . $g->model['insurance_record_id'];
			$g->current_row_html[$f] = '<input type="checkbox" id="cb_' . $id . '" name="cb_' . $id . '" value="' . $id . '">';

			$g->setTDParam('select', 'data-insuranceid', $g->model['insurance_record_id']);
		});
		$grid->addColumn('select', 'select');

		// manually call grid selectabel value
		$grid->js_widget = null;
		$grid->js(true)
			->_load('ui.atk4_checkboxes')
			->atk4_checkboxes(array('dst_field' => $field_accounts));
		$grid->addOrder()
			->useArray($grid->columns)
			->move('select', 'first')
			->now();

		$grid->addFormatter('address', 'wrap');

	}

	function getQueryString($account_array, $ins_form) {

		$query = 'INSERT INTO `member_insurance`(`member_id`, `accounts_id`, `name`, `insurance_start_date`, `insurance_duration`, `narration`, `next_insurance_due_date`) VALUES ';
		$member_insurance_ids = [];
		foreach ($account_array as $key => $ids) {
			$id_array = explode("_", $ids);

			$account_id = $id_array[0];
			if (isset($id_array[1]) && $id_array[1] > 0) {
				$member_insurance_ids[] = $id_array[1];
			}

			$model_acocunt = $this->add('Model_Account')->load($account_id);
			$member_id = $model_acocunt['member_id'];

			$next_insurance_due_date = date('Y-m-d', strtotime("+" . $ins_form['insurance_duration'] . " year", strtotime($ins_form['insurance_date'])));

			$query .= '(';
			$query .= $member_id . ",";
			$query .= $account_id . ",";
			$query .= '"-",';
			$query .= '"' . $ins_form['insurance_date'] . '",';
			$query .= $ins_form['insurance_duration'] . ",";
			$query .= '"' . $ins_form['narration'] . '",';
			$query .= '"' . $next_insurance_due_date . '"';
			$query .= '),';
		}
		$query = trim($query, ',');
		$query .= ';';

		if (count($member_insurance_ids)) {
			$query_insurance = "UPDATE `member_insurance` SET `is_renew` = '1' WHERE `member_insurance`.`id` in ";
			$str = "(";
			foreach ($member_insurance_ids as $key => $id) {
				$str .= "'" . $id . "',";
			}
			$str = trim($str, ',');
			$str .= ");";
			$query_insurance .= $str;
			$query .= $query_insurance;
		} else {
			$query_insurance = 0;
		}

		return $query;
	}

}