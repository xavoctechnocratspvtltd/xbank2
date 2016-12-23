<?php
class Form_Field_Line extends Form_Field {
	function init(){
		parent::init();
		$this->setAttr('style','text-transform:uppercase');
	}
    function getInput($attr=array()){
        return parent::getInput(array_merge(array('type'=>'text'),$attr));
    }
}
