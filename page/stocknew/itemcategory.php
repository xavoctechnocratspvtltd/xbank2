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
		$item_crud->grid->addPaginator(100);
		
		// ====== Categories ========
		$crud=$category_tab->add('xCRUD');

		$crud->addHook('myupdate',function($crud,$form){

			$m = $form->getModel();
			if($crud->isEditing('edit')) $m->load($crud->id);
			
			$m['name'] = $form['name'];

			$tt = $this->add('Model_StockNew_TransactionTemplate');
			$allowed_transactions = [];
			foreach ($tt as $t) {
				if($form[$tt['name']]){
					$allowed_transactions[] = $tt['name'];
				}
			}

			$m['allowed_in_transactions'] = json_encode($allowed_transactions);

			$m->save();
			return true;
			
		});

		$category=$this->add('Model_StockNew_Category');
		$category->add('Controller_Acl');

		if($f= $crud->form){
			$f->add('View')->set('Allow this Category Items in Transactions');
			$tt = $this->add('Model_StockNew_TransactionTemplate');
			foreach ($tt as $t) {
				$f->addField('CheckBox',$tt['name']);
			}
		}

		$crud->setModel($category,['name'],['name','allowed_in_transactions']);
		$ref_item_crud = $crud->addRef('StockNew_Item');

		if($f=$crud->form){
			$f->add('Order')->move('name','first')->now();
		}
	
		if($g=$crud->grid){
			$g->addQuickSearch(array('name'));
			$g->addPaginator(200);
		}


		if($ref_item_crud){
			$ref_item_crud->grid->addPaginator(100);
		}
	}
}