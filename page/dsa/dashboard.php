<?php

class page_dsa_dashboard extends Page {
	
	function init(){
		parent::init();
		$this->title = $this->api->auth->model['name'];

		$tabs = $this->add('Tabs');
		
		// Dealers
		$dealers_tab = $tabs->addTab('Dealers');
		
		$grid = $dealers_tab->add('Grid');
		$grid->setModel($this->api->auth->model->ref('Dealer'));

		// $dealers_tab = $tabs->addTab('Dealers');


	}
}