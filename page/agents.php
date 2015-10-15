<?php

class page_agents extends Page{
	public $title ="Agent Management";

	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$crud = $this->add('CRUD');
		$agent=$this->add('Model_Agent');
		$agent->setOrder('id','desc');

		if($crud->isEditing('edit')){
			$agent->hook('editing');
		}


		$crud->setModel($agent,array('member_id','sponsor_id','account_id','cadre_id','ActiveStatus'),array('code','member','sponsor','sponsor_cadre','account','cadre','current_individual_crpb','ActiveStatus'));

		if($crud and !$crud->isEditing('add') and ! $crud->isEditing('edit')){
			$crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentDocuments'));
			
			$agent_guarantor_crud = $crud->addRef('AgentGuarantor');
			if($agent_guarantor_crud and !$agent_guarantor_crud->isEditing('add') and !$agent_guarantor_crud->isEditing('edit')){
				$agent_guarantor_crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentGuarantor'));
				$agent_guarantor_crud->add('Controller_Acl');
			}

			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('member','account','code'));
		}


		if($crud->isEditing('add')){			//TODO 
			
			$account_of_member_field = $crud->form->getElement('account_id');
			
			$account_of_member_field->send_other_fields = array($crud->form->getElement('member_id'));
			if($member_selected = $_GET['o_'.$crud->form->getElement('member_id')->name]){
				$account_of_member_field->model->addCondition('member_id',$member_selected);
				$account_of_member_field->model->addCondition('ActiveStatus',true);
			}

		}

		if($crud->isEditing('edit')){			//TODO 
			
			$account_of_member_field = $crud->form->getElement('account_id');
			$account_of_member_field->model->addCondition('member_id',$crud->form->model['member_id']);
			// $account_of_member_field->model->addCondition('ActiveStatus',true);
		}

		$crud->add('Controller_Acl');
	}
}