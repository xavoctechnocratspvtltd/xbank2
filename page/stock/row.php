<?php

class page_stock_row extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('allow_edit'=>false));
		$row = $this->add('Model_Stock_Row');
		// $row->addCondition('container_id','<>',$container['id']);


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
			$m = $crud->form->getElement('container_id')->getModel();
			$m->add('Controller_Acl');
			$m->addCondition('name','<>','General');
			$m->addCondition('name','<>','Dead');
		}
	
		if($g=$crud->grid){
			$g->addQuickSearch(array('container','name'));	
			$g->addPaginator(100);
		}
	}
}