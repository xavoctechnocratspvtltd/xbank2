<?php

class page_dsa_dealers extends Page{
	function init(){
		parent::init();

		$grid = $this->add('Grid');
		$grid->setModel($this->api->auth->model->ref('Dealer'));
	}
}