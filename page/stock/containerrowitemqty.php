<?php

class page_stock_containerrowitemqty extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('allow_del'=>false,'allow_edit'=>false));

		$criq=$this->add('Model_Stock_ContainerRowItemQty');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$criq_model = $crud->add('Model_Stock_ContainerRowItemQty');
			// CreatNew Function call
			$criq_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required
		});
		
		$crud->setModel($criq);		
	
		if($g=$crud->grid){
			$g->addPaginator(50);

		}

	}
}