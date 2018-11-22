<?php


class Form_Field_RichText extends Form_Field_Text {

	public $options=[];
	public $js_widget='xepan_richtext_admin';

	function init(){
		parent::init();

	}

	function render(){

		$this->js(true)
				->_load('tinymce/tinymce.min')
				->_load('tinymce/jquery.tinymce.min')
				->_load('tinymce/xepan3.tinymce')
				;
		$this->js(true)->univ()->xtinymce($this);
		parent::render();
	}
}