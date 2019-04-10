<?php

class page_stocknew_rowscontainers extends Page {
	public $title = "Manage Rows, Containers";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$tabs = $this->add('Tabs');
		$container_rows_tab  = $tabs->addTab('Containers & Rows');
		$container_types_tab = $tabs->addTab('Container Types');

		$model = $this->add('Model_StockNew_Container');
		$branch_field = $model->getElement('branch_id');
		$branch_field->getModel()->addCondition('id',$this->app->current_branch->id);
		$branch_field->defaultValue($this->app->current_branch->id);

		$crud = $container_rows_tab->add('CRUD');
		$crud->setModel($model);
		$crud->add('Controller_Acl',['default_view'=>false]);

		$row_crud = $crud->addRef('StockNew_ContainerRow');
		if($row_crud instanceof CRUD)
			$row_crud->add('Controller_Acl',['default_view'=>false]);

		$crud = $container_types_tab->add('CRUD');
		$crud->setModel('StockNew_ContainerType');
		$crud->add('Controller_Acl',['default_view'=>false]);


	}
}