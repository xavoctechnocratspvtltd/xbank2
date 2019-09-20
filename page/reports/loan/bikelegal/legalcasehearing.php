<?php

class page_reports_loan_bikelegal_legalcasehearing extends Page {
	public $title = "Legal Case Hearing Report";

	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','account')->setModel('Account');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$ct_field = $form->addField('DropDown','case_type')->setValueList(array_combine(LEGAL_CASE_TYPES, LEGAL_CASE_TYPES));
		$ct_field->setAttr('multiple','multiple');
		$cst_field = $form->addField('DropDown','stage')->setValueList(array_combine(LEGAL_CASE_STAGES, LEGAL_CASE_STAGES));
		$cst_field->setAttr('multiple','multiple');
		$form->addField('DropDown','case_on')->setEmptyText('Any')->setValueList(array_combine(['Owner','Guarantor'], ['Owner','Guarantor']));

		$form->addSubmit('Go');

		$model = $this->add('Model_LegalCaseHearing');


		if($account = $this->app->stickyGET('account')){
			$model->addCondition('account_id',$account);
		}

		if($from_date = $this->app->stickyGET('from_date')){
			$model->addCondition('hearing_date','>=', $from_date);
		}

		if($to_date = $this->app->stickyGET('to_date')){
			$model->addCondition('hearing_date','<', $this->app->nextDate($to_date));
		}

		if($case_type = $this->app->stickyGET('case_type')){
			$model->addCondition('case_type', explode(",",$case_type));
		}

		if($stage = $this->app->stickyGET('stage')){
			$model->addCondition('stage', explode(",",$stage));
		}

		if($case_on = $this->app->stickyGET('case_on')){
			$model->addCondition('case_on', $case_on);
		}


		$grid = $this->add('Grid');
		$grid->addSno();

		$field = ['account','owner','dealer','name','court','bccs_file_no','legal_filing_date','court','case_on','hearing_date','advocate','account_guarantor','autorised_person','stage'];
		$grid->setModel($model,$field);

		if($form->isSubmitted()){
			$form_data= $form->get();
			$form_data['from_date'] = $form['from_date']?:0;
			$form_data['to_date'] = $form['to_date']?:0;
			$grid->js()->reload($form_data)->execute();
		}

	}
}