<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
			
		$this->add('Model_Branch')->load(2)->performClosing();

	}
}