<?php

class page_noclog_report extends Page {
	public $title='NOC Report';

	function init(){
		parent::init();
		
		$report_type_list = [
						'noc_not_made_due_to'=>'Noc Not Made Due To',
						'noc_lying_in_branch'=>'Noc Lying in Branch',
						'noc_dispatched'=>'NOC Dispatched',
						'noc_return_by_branch'=>'NOC Return By Branch'
					];

		$filter = $this->app->stickyGET('filter');
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$branch = $this->app->stickyGET('branch');
		$account = $this->app->stickyGET('account');
		$report_type = $this->app->stickyGET('report_type');
		

		$view = $this->add('View')->setStyle('width','900px');
		$form = $view->add('Form',null,null,['form/horizontal']);
		$from_date_field = $form->addField('DatePicker','from_date');
		$to_date_field = $form->addField('DatePicker','to_date');
		$branch_field = $form->addField('DropDown','branch');
		$account_field = $form->addField('autocomplete/Basic','account');
		$report_type_field = $form->addField('DropDown','report_type');

		$form->addSubmit('Filter');

		$branch_model = $this->add('Model_Branch')->addCondition('id','<>',$this->app->current_branch->id);
		$branch_field->setModel($branch_model);
		$branch_field->setEmptyText('Please Select');

		$account_model = $this->add('Model_Account_Loan');
		$account_field->setModel($account_model);

		$report_type_field->setValueList($report_type_list);
		$report_type_field->setEmptyText('Please Select');

		$this->add('View')->setElement('hr');

		$grid = $this->add('Grid');
		$model = $this->add('Model_NocLog');
		$model->addExpression('account_number')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('AccountNumber')]);
		});
		$model->addExpression('member_name')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('member_name_only')]);
		});
		$model->addExpression('member_id')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('member_id')]);
		});

		$model->addExpression('mobile_no')->set(function($m,$q){
			$member_model = $m->add('Model_Member')->addCondition('id',$m->getElement('member_id'));
			return $q->expr('[0]',[$member_model->fieldQuery('PhoneNos')]);
		});

		$model->addExpression('duration')->set(function($m,$q){

			return $q->expr('DATEDIFF([],[created_on])',[
						'created_on'=>$m->getElement('send_at'),
					]);
		});

		// duration
		// if dispatched then dispatched_date
		// if return then return date
		// else today;

		if($filter){

		}

		$grid->addSno();
		$grid->setModel($model);

		// grid column
		// sn_no | Account_no | member_name | Noc Letter Receive Date |noc sent by| send_date | Received_by | Received_date | Noc Dispatched Detail | Return Detail | AC deactivated date
	}
}