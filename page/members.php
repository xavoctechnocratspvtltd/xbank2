<?php

class page_members extends Page {
	
	public $title='Member Management';

	function init(){
		parent::init();

		$crud = $this->add('xCRUD');

		$member_model = $this->add('Model_Member');
		$member_model->add('Controller_Acl');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$new_member_model = $crud->add('Model_Member');
			
			$new_member_model->createNewMember($form['name'], $admissionFee=10, $shareValue=100, $branch=null, $other_values=$form->getAllFields(),$form,$on_date=null);
			return true;
		});

		if($crud->isEditing()){
			$member_model->getElement('created_at')->system(true);
			$member_model->getElement('updated_at')->system(true);
			$member_model->getElement('is_agent')->system(true);
		}
		
		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('edit')){
			$member_model->hook('editing');
		}

		$crud->setModel($member_model);
		if($g= $crud->grid) {
			$g->addQuickSearch(array('name'));
			$g->addMethod('format_removeEdit',function($grid,$field){
				if($grid->model['name'] == $grid->model->ref('branch_id')->get('Code').SP.'Default')
					$grid->current_row_html[$field]='';
			});
			$g->addMethod('format_removeDelete',function($grid,$field){
				if($grid->model['name'] == $grid->model->ref('branch_id')->get('Code').SP.'Default')
					$grid->current_row_html[$field]='';
			});
			$g->addFormatter('edit','removeEdit');
			$g->addFormatter('delete','removeDelete');
			$g->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}
	}
}