<?php

class page_agents extends Page{
	public $title ="Agent Management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$crud->setModel('Agent');

		if($crud and !$crud->isEditing()){
			$crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentDocuments'));
			$agent_guarantor_crud = $crud->addRef('AgentGuarantor');
			if($agent_guarantor_crud and $agent_guarantor_crud->grid){
				$agent_guarantor_crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentGuarantor'));
			}

			$crud->grid->addPaginator(50);
		}

	}

}