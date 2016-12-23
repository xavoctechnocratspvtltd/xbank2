<?php

class page_stock_item extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');

		$item=$this->add('Model_Stock_Item');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false; // Always required to bypass the bellow code in editing crud mode
			
			// Do your stuff by getting $form data
			$item_model = $crud->add('Model_Stock_Item');
			// CreatNew Function call
			$item_model->createNew($form['name'],$form->getAllFields(),$form);
			return true; // Always required

			$container_field=$form->getElement('container_id');
			$row_field=$form->getElement('row_id');
			throw $this->exception($container_field, 'ValidityCheck')->setField('FieldName'); 

			
		});
		
		// $item->getElement('category_id')->system(true);		
		$crud->setModel($item);	
		
		if($form=$crud->form){
			$c_f = $form->getElement('container_id');
			$r_f = $form->getElement('row_id');

			if($_GET['container_id']){
				$r_f->getModel()->addCondition('container_id',$_GET['container_id']);
			}

			$c_f->js('change',$form->js()->atk4_form('reloadField',$r_f,array($this->api->url(),'container_id'=>$c_f->js()->val())));
		}

		if($g=$crud->grid){
			$g->addPaginator(10);
			// $g->removeColumn('category');
		}
	}
}