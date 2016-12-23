<?php

class page_stock_member extends Page {
	function init(){
		parent::init();

		$this->add('Controller_Acl',array('default_view'=>false));

		$crud=$this->add('xCRUD');

		$party=$this->add('Model_Stock_Member');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$party_model = $crud->add('Model_Stock_Member');
			// CreatNew Function call
			$party_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required
		});
		
		$crud->setModel($party);		
	
		if($g=$crud->grid){
			$g->addPaginator(100);
		}

		$crud->add('Controller_Acl');
	}
}