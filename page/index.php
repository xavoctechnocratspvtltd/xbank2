<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
		
		$this->add('View_DuesReceiveList');
		$this->add('View_DuesGiveList');

	}
}