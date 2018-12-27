<?php
class page_stocknew_itemcategory extends Page {
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$item_tab = $tabs->addTab('Items');
		$category_tab = $tabs->addTab('Item Categories');


		// ====== Items ========
		$item_crud = $item_tab->add('CRUD');
		$item_crud->setModel('Model_StockNew_Item');
		if($item_crud->isEditing()){
			$at_field = $item_crud->form->getElement('allowed_transactions')->setAttr('multiple','multiple');
			$at_field->set(explode(",",$item_crud->model['allowed_transactions']));
		}else{
			$item_crud->grid->removeColumn('allowed_transactions');
		}

		
		// ====== Categories ========
		$crud=$category_tab->add('CRUD');

		$container=$this->add('Model_StockNew_Category');
		$container->add('Controller_Acl');
		
		$crud->setModel($container);
		$ref_item_crud = $crud->addRef('StockNew_Item');
		if($ref_item_crud){
			if($ref_item_crud->isEditing()){
				$at_field = $ref_item_crud->form->getElement('allowed_transactions')->setAttr('multiple','multiple');
				$at_field->set(explode(",",$ref_item_crud->model['allowed_transactions']));
			}else{
				$ref_item_crud->grid->removeColumn('allowed_transactions');
			}
		}
	
		if($g=$crud->grid){
			$g->addQuickSearch(array('name'));
			$g->addPaginator(200);
		}
	}
}