<?php

class page_agents extends Page{
	public $title ="Agent Management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$agent=$this->add('Model_Agent');
		$agent->setOrder('id','desc');

		if($crud->isEditing('edit')){
			$agent->hook('editing');
		}


		$crud->setModel($agent,array('member_id','sponsor_id','account_id','cadre_id'),array('member','sponsor','account','cadre','ActiveStatus'));

		if($crud and !$crud->isEditing()){
			$crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentDocuments'));
			
			$agent_guarantor_crud = $crud->addRef('AgentGuarantor');
			if($agent_guarantor_crud and !$agent_guarantor_crud->isEditing()){
				$agent_guarantor_crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentGuarantor'));
			}

			$crud->grid->addPaginator(50);
		}


		if($crud->isEditing('add')){			//TODO 
			
			$account_of_member_field = $crud->form->getElement('account_id');
			
			$account_of_member_field->send_other_fields = array($crud->form->getElement('member_id'));
			if($member_selected = $_GET['o_'.$crud->form->getElement('member_id')->name]){
				$account_of_member_field->model->addCondition('member_id',$member_selected);
				$account_of_member_field->model->addCondition('ActiveStatus',true);
			}

	}

}
}