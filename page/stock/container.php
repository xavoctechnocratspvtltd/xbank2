<?php
class page_stock_container extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('allow_edit'=>false));

		$container=$this->add('Model_Stock_Container');
		$container->add('Controller_Acl');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode			
			// Do your stuff by getting $form data
			$container_model = $crud->add('Model_Stock_Container');
			// CreatNew Function call
			$container_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required

			
		});
		
		$crud->setModel($container);		
	
		if($g=$crud->grid){
			$g->addQuickSearch(array('name'));
			$g->addPaginator(200);
		}
	}
}