<?php

class page_members extends Page {
	
	public $title='Member Management';

	function init(){
		parent::init();

		$crud = $this->add('xCRUD');

		$member_model = $this->add('Model_Member');
		$member_model->add('Controller_Acl');
		$member_model->setOrder('id','desc');

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

		if($crud->isEditing('edit')){
			$member_model->getElement('Nominee')->system(true);
			$member_model->getElement('RelationWithNominee')->system(true);
			$member_model->getElement('NomineeAge')->system(true);
			$member_model->hook('editing');
		}
		
		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		    $crud->form->addField('CheckBox','open_share_account');
		}


		$crud->setModel($member_model);
		if(!$crud->isEditing()) {
			$g=$crud->grid;
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

		if($crud->isEditing()){
			$is_minor_field = $crud->form->getElement('IsMinor');
			$is_minor_field->js(true)->univ()->bindConditionalShow(array(
				''=>array(''),
				'*'=>array('MinorDOB','ParentName','RelationWithParent','ParentAddress')
				),'div .atk-form-row');

			$form_60_field = $crud->form->getElement('FilledForm60');
			$form_60_field->js(true)->univ()->bindConditionalShow(array(
				''=>array('PanNo'),
				'*'=>array()
				),'div .atk-form-row');

		}

		if($crud->isEditing('add')){
		    $o->move('open_share_account','before','Nominee');
			$open_share_account_field = $crud->form->getElement('open_share_account');
			$open_share_account_field->js(true)->univ()->bindConditionalShow(array(
				''=>array(''),
				'*'=>array('Nominee','RelationWithNominee','NomineeAge')
				),'div .atk-form-row');
			$o->now();
		}
	}
}