<?php

class page_ajaxhandler extends Page{
	function init(){
		parent::init();
		throw new Exception($_GET['item'], 1);
		
		$this->api->stickyGET('item');
		$item=$this->add('Model_Stock_Item');
		$item->load($_GET['item']);
		$getAvgRate=$item->getAvgRate();
		return $getAvgRate;
		// throw new Exception($getAvgRate, 1);
		
	}
}