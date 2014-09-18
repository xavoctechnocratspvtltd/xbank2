<?php

class page_agents extends Page{
	public $title ="Agent Management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$agent=$this->add('Model_Agent');
		$agent->setOrder('id','desc');
		$crud->setModel($agent);

		if($crud and !$crud->isEditing()){
			$crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentDocuments'));
			$agent_guarantor_crud = $crud->addRef('AgentGuarantor');
			if($agent_guarantor_crud and $agent_guarantor_crud->grid){
				$agent_guarantor_crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentGuarantor'));
			}

			$crud->grid->addPaginator(50);
		}


		if($crud->isEditing('add')){			//TODO 
			
			$account_of_member_field = $crud->form->getElement('account_id');
			
			$account_of_member_field->send_other_fields = array($crud->form->getElement('member_id'));
			if($member_selected = $_GET['o_'.$crud->form->getElement('member_id')->name]){
				$account_of_member_field->model->addCondition('member_id',$member_selected);
			}

	}

}
}