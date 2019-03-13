<?php

class page_noclog_new extends Page {
	public $title='Dispatch New NOC';

	function init(){
		parent::init();
		
		$noc_model = $this->add('Model_NocLog');
		$noc_model->addCondition('created_by_id',$this->app->current_branch->id);

		$crud = $this->add('CRUD',['form_class'=>'Form_Stacked']);
		$crud->setModel($noc_model,['accounts_id','noc_letter_received_on','to_branch_id','send_narration'],['accounts','noc_letter_received_on','to_branch','created_by','send_at','send_narration']);
		
		if($crud->isEditing('add')){
			$form = $crud->form;
			// $form->getElement('accounts_id')->getModel()->addCondition('id','<>',$this->app->current_branch->id);
			$form->getElement('to_branch_id')->getModel()->addCondition('id','<>',$this->app->current_branch->id);
		}
		
		$crud->grid->addFormatter('send_narration','wrap');
		$crud->grid->addFormatter('accounts','Wrap');

	}
}