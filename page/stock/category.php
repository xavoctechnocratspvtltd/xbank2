<?php

class page_stock_category extends Page {
	function init(){
		parent::init();

		$this->add('Controller_Acl',array('default_view'=>false));
		$crud=$this->add('xCRUD',array('allow_edit'=>false));

		$category=$this->add('Model_Stock_Category');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$category_model = $crud->add('Model_Stock_Category');
			// CreatNew Function call
			$category_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required
		});
		
		$crud->setModel($category);		
	
		if($g=$crud->grid){
			$g->addQuickSearch(array('name'));		
			$g->addPaginator(20);
		}

		$crud->add('Controller_Acl');

	}
}