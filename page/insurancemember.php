<?php

class page_insurancemember extends Page {
	public $title = "Member Insurance";
	function init() {
		parent::init();

		$this->add('Controller_Acl', ['default_view' => false]);
		$tab = $this->add('Tabs');
		$tab->addTabUrl('allinsurance', 'Member Insurance');
		$tab->addTabUrl('newrenewinsurance', 'New/ Renew Member Insurance');

		// $member_id = $this->app->stickyGET('selected_member_id');
		// $insurance_model = $mi_tab->add('Model_MemberInsurance');
		// $insurance_model->setOrder('id','desc');

		// $crud = $mi_tab->add('CRUD');
		// $crud->setModel($insurance_model,['accounts_id','name','insurance_start_date','insurance_duration','narration','next_insurance_due_date'],['account_number','member','name','insurance_start_date','insurance_duration','narration','next_insurance_due_date']);
		// if($crud->isEditing('add') OR $crud->isEditing('edit')){
		// 	$form = $crud->form;
		// 	$account_field = $form->getElement('accounts_id');
		// 	$account_model = $account_field->getModel();
		// 	$account_model->addCondition('DefaultAC',false);
		// }
		// $crud->grid->addQuickSearch(['name','member','account_number']);
		// $crud->grid->addPaginator(50);
		// $crud->add('Controller_Acl');

	}
}