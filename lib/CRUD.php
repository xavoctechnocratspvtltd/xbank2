<?php

class CRUD extends View_CRUD{
	function init(){
		parent::init();
		$this->setAttr('style','text-transform:uppercase');
	}
}