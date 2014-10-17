<?php

class page_stock_row extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');

		$row=$this->add('Model_Stock_Row');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$row_model = $crud->add('Model_Stock_Row');
			// CreatNew Function call
			$row_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required
		});
		
		$crud->setModel($row);		

		if($crud->isEditing()){
			$crud->form->getElement('container_id')->getModel()->add('Controller_Acl');
		}
	
		if($g=$crud->grid){
			$g->addPaginator(10);

		}
	}
}