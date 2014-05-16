<?php

class page_members extends Page {
	
	public $title='Member Management';

	function init(){
		parent::init();

		$crud = $this->add('xCRUD');

		$member_model = $this->add('Model_Member');
		$member_model->add('Cotroller_Acl');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$new_member_model = $crud->add('Model_Member');
			
			$new_member_model->createNewMember($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			return true;
		});
		
		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('edit')){
			$account_cc_model->hook('editing');
		}

		$crud->setModel($member_model);
		if($g= $crud->grid) {
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