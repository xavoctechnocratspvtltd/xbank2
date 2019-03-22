<?php

class page_allinsurance extends Page{
	public $title = "Member Insurance";
	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$insurance_model = $this->add('Model_MemberInsurance');
		$insurance_model->setOrder('id','desc');
		$crud->setModel($insurance_model,['accounts_id','name','insurance_start_date','insurance_duration','narration','next_insurance_due_date'],['account_number','member','name','insurance_start_date','insurance_duration','narration','next_insurance_due_date']);
		if($crud->isEditing('add') OR $crud->isEditing('edit')){
			$form = $crud->form;
			$account_field = $form->getElement('accounts_id');
			$account_model = $account_field->getModel();
			$account_model->addCondition('DefaultAC',false);
		}
		$crud->grid->addQuickSearch(['name','member','account_number']);
		$crud->grid->addPaginator(50);
		$crud->add('Controller_Acl',['default_view'=>true]);

		
	}
}