<?php

class page_ajaxhandler extends Page{
	function init(){
		parent::init();

		$this->api->stickyGET('item');
		$item=$this->add('Model_Stock_item');
		$getAvgRate=$item->getAvgRate();
		return $getAvgRate;
	}
}