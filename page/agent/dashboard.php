<?php

class page_agent_dashboard extends Page{
	function init(){
		parent::init();
		$this->title = $this->api->auth->model['name'];


		// $this->add('Grid')->setModel('Model_Member');
	}	
}