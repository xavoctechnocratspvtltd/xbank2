<?php

class page_agentscadre extends Page{
	public $title ="Agent Cadre Management";

	function init(){
		parent::init();

		$crud = $this->add('CRUD');
		$agent=$this->add('Model_Cadre');
		$agent->setOrder('id','desc');
		$crud->setModel($agent);
	}
}