<?php

class page_utility_setdate extends Page {
	public $title ='Change The Working Date';
	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		$form=$this->add('Form');
		$form->addField('DatePicker','date')->set($this->api->today)->validateNotNull();
		$form->addSubmit('Change');

		if($form->isSubmitted()){
			$this->api->setDate($form['date']);
			$this->js()->redirect()->execute();
		}
	}
}