<?php

class page_dsa_dashboard extends Page {
	
	function init(){
		parent::init();
		$this->title = $this->api->auth->model['name'];

		$tabs = $this->add('Tabs');
		$dealers_tab = $tabs->addTab('Dealers');
		$dealers_tab = $tabs->addTab('Dealers');
		$dealers_tab = $tabs->addTab('Dealers');
		$dealers_tab = $tabs->addTab('Dealers');

	}
}