<?php

class page_stock_party extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');

		$party=$this->add('Model_Stock_Party');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$party_model = $crud->add('Model_Stock_Party');
			// CreatNew Function call
			$party_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required
		});
		
		$crud->setModel($party);		
	
		if($g=$crud->grid){
			$g->addPaginator(10);

		}
	}
}