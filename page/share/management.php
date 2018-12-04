<?php


class page_share_management extends Page {
	public $title = "Share Management";


	function page_index(){
		$tabs = $this->add('Tabs');
		$tabs->addTabURL($this->app->url('./share'),'Share');
		$tabs->addTabURL($this->app->url('./certificate'),'Share Certificates');
	}

	function page_share(){

		$model = $this->add('Model_Share');
		$crud = $this->add('CRUD');

		$crud->setModel($model);

		$crud->grid->addPaginator(200);

		$his_crud = $crud->addRef('ShareHistory');

		// if($his_crud){
		// 	$his_crud->getModel()->debug();
		// }

	}

	function page_certificate(){

		$model = $this->add('Model_ShareCertificate');
		$crud = $this->add('CRUD');

		$crud->setModel($model);

		$crud->grid->addPaginator(200);
		$crud->grid->addQuickSearch(['name']);
		$his_crud = $crud->addRef('Share');
	}
}