<?php

class page_dsa extends Page {
	public $title ='DSA Management';
	
	function init(){
		parent::init();

		$dsa_model = $this->add('Model_DSA');
		$crud = $this->add('CRUD');
		$crud->setModel($dsa_model);

		if($crud and !$crud->isEditing()){
			$crud->add('Controller_DocumentsManager',array('doc_type'=>'DSADocuments'));
			$dsa_guarantor_crud = $crud->addRef('DSAGuarantor');
			if($dsa_guarantor_crud and $dsa_guarantor_crud->grid){
				$dsa_guarantor_crud->add('Controller_DocumentsManager',array('doc_type'=>'DSAGuarantor'));
			}
			
		}

	}
}