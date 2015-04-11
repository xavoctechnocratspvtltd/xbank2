<?php

class Menu_Dsa extends Menu {
	function init(){
		parent::init();

		$this->addMenuItem('utility_setdate',array('('.$this->api->current_branch['Code']. ') ' .date('d M Y',strtotime($this->api->today)),'swatch'=>(strtotime($this->api->today) != strtotime(date('Y-m-d')) ? 'red':null )));

		$this->addMenuItem('index','Dashboard');
		

	}
}