<?php

class xCRUD extends View_CRUD {
	function formSubmit($form){
		$this->hook('myupdate',array($form));
		
		parent::formSubmit($form);
	}
}