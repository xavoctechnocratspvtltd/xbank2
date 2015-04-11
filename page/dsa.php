<?php

class page_dsa extends Page {
	public $title ='DSA Management';
	
	function init(){
		parent::init();

		$this->add('Controller_Acl');
		$dsa_model = $this->add('Model_DSA');
		$crud = $this->add('CRUD');
		$crud->setModel($dsa_model);

		if($crud and !$crud->isEditing('add') and !$crud->isEditing('edit')){
			$crud->add('Controller_DocumentsManager',array('doc_type'=>'DSADocuments'));
			$dsa_guarantor_crud = $crud->addRef('DSAGuarantor');
			if($dsa_guarantor_crud and !$dsa_guarantor_crud->isEditing('add') and !$dsa_guarantor_crud->isEditing('edit')){
				$dsa_guarantor_crud->add('Controller_DocumentsManager',array('doc_type'=>'DSAGuarantor'));
				$dsa_guarantor_crud->add('Controller_Acl');
			}
			
		}

		$crud->add('Controller_Acl');
	}
}