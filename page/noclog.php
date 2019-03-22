<?php

class page_noclog extends Page {
	public $title= 'NOC Management';

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$tabs->addTabURL('noclog_new','Send NOC');
		$tabs->addTabURL('noclog_receive','Receive NOC');
		$tabs->addTabURL('noclog_returnreceive','Return NOC Receive');

	}
}