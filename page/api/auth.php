<?php


/**
* 
*/
class page_api_auth extends Page{
	
	function init(){
		parent::init();

		$this->app->header->destroy();
	}
}