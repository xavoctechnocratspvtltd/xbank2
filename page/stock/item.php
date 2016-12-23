<?php

class page_stock_item extends Page {
	function init(){
		parent::init();

		$this->add('Controller_Acl',array('default_view'=>false));
		$crud=$this->add('xCRUD');

		$item=$this->add('Model_Stock_Item');
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$item_model = $crud->add('Model_Stock_Item');
			// CreatNew Function call
			$item_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required

			
		});
		
		$crud->setModel($item);		
	
		if($g=$crud->grid){
			$g->addQuickSearch(array('name','category'));	
			$g->addPaginator(300);
		}

		$crud->add('Controller_Acl');
	}
}