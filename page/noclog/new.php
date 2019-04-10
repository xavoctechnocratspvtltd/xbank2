<?php

class page_noclog_new extends Page {
	public $title='NOC Dispatch Management';

	function init(){
		parent::init();
		
		$this->add('Controller_Acl');

		$noc_model = $this->add('Model_NocLog');

		$noc_model->addCondition('created_by_id',$this->app->current_branch->id);
		$noc_model->addCondition([['received_by_id',null],['received_by_id',0]]);
		$noc_model->setOrder('send_at','desc'); 

		$crud = $this->add('CRUD',['form_class'=>'Form_Stacked']);

		$crud->grid->addSno();
		$crud->setModel($noc_model,['accounts_id','noc_letter_received_on','to_branch_id','send_narration','is_dispatch_to_customer','dispatch_narration','is_noc_not_made','noc_not_made_due_to','is_noc_hold','noc_hold_due_to'],['accounts','noc_letter_received_on','to_branch','created_by','send_at','send_narration','is_dispatch_to_customer','is_return']);
		
		if($crud->isEditing('add')){
			$form = $crud->form;
			$field_account_model = $form->getElement('accounts_id')->getModel();
			$field_account_model->addCondition([['DefaultAC',false],['DefaultAC',null]]);
			$send_ids = $noc_model->getSendNocIds();
			if($send_ids)
				$field_account_model->addCondition('id','<>',$send_ids);
			$form->getElement('to_branch_id')->getModel()->addCondition('id','<>',$this->app->current_branch->id);
		}
		
		$crud->grid->addHook('formatRow',function($g){
			$g->current_row_html['to_branch'] = $g->model['to_branch'] ." <br/>By: ".$g->model['created_by']."<br/>send On: ".$g->model['send_at']."<br/>Narration: ".$g->model['send_narration'];
		});

		$crud->grid->addFormatter('send_narration','wrap');
		$crud->grid->addFormatter('accounts','Wrap');

		$crud->grid->addQuickSearch(['accounts','send_narration']);
		$crud->grid->addPaginator(20);

		$crud->grid->removeColumn('created_by');
		$crud->grid->removeColumn('send_at');
		$crud->grid->removeColumn('send_narration');
		$crud->add('Controller_Acl',['default_view'=>false]);
	}
}