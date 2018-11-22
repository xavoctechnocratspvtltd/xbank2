<?php



class page_test  extends Page {
	
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('RichText','test');

	}
}
