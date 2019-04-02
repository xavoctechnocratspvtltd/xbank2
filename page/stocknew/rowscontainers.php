<?php

class page_stocknew_rowscontainers extends Page {
	public $title = "Manage Rows, Containers";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$tabs = $this->add('Tabs');
		$container_rows_tab  = $tabs->addTab('Containers & Rows');
		$container_types_tab = $tabs->addTab('Container Types');

		$crud = $container_rows_tab->add('CRUD');
		$crud->setModel('StockNew_Container');

		$crud->addRef('StockNew_ContainerRow');

		$crud = $container_types_tab->add('CRUD');
		$crud->setModel('StockNew_ContainerType');

	}
}