<?php
class page_stocknew_itemcategory extends Page {
	
	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$tabs = $this->add('Tabs');
		$item_tab = $tabs->addTab('Items');
		$category_tab = $tabs->addTab('Item Categories');


		// ====== Items ========
		$item_model = $this->add('Model_StockNew_Item');
		$item_model->addExpression('current_location')->set(function($m,$q){
			$st = $m->add('Model_StockNew_Transaction');
			$st->addCondition('item_id',$m->getElement('id'));
			$st->setLimit(1);
			$st->setOrder('id','desc');
			return $q->expr('IF([0],(IF([1]="ISSUE",[2],concat([3]," :: ",[4])))," ")',[
					$m->getElement('is_fixed_asset'),
					$st->fieldQuery('transaction_template_type'),
					$st->fieldQuery('to_member'),
					$st->fieldQuery('to_container'),
					$st->fieldQuery('to_container_row'),
				]);
		});

		$item_crud = $item_tab->add('CRUD');
		$item_crud->setModel($item_model);
		$item_crud->grid->addPaginator(500);
		$item_crud->grid->addQuickSearch(['name','code']);
		$item_crud->add('Controller_Acl',['default_view'=>false]);

		// ====== Categories ========
		$crud = $category_tab->add('xCRUD');

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

		if($crud->isEditing('add') OR $crud->isEditing('edit')){
			if($f= $crud->form){
				$f->add('View')->set('Allow this Category Items in Transactions');
				$tt = $this->add('Model_StockNew_TransactionTemplate');
				foreach ($tt as $t) {
					$f->addField('CheckBox',$tt['name']);
				}
			}
		}

		$crud->setModel($category,['name'],['name','allowed_in_transactions']);
		$crud->add('Controller_Acl',['default_view'=>false]);
		$ref_item_crud = $crud->addRef('StockNew_Item');

		if($crud->isEditing('add') OR $crud->isEditing('edit')){
			if($f = $crud->form){
				if($f->hasElement('name'))
					$f->add('Order')->move('name','first')->now();
			}
		}
	
		if($g=$crud->grid){
			$g->addQuickSearch(array('name'));
			$g->addPaginator(200);
		}

		if($ref_item_crud){
			$ref_item_crud->grid->addPaginator(100);
			$ref_item_crud->add('Controller_Acl',['default_view'=>false]);
		}
	}
}