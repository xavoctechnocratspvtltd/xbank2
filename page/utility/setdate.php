<?php

class page_utility_setdate extends Page {
	public $title ='Change The Working Date';
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','date')->set($this->api->today)->validateNotNull();
		$form->addSubmit('Change');

		if($form->isSubmitted()){
			$this->api->setDate($form['date']. " ". date("H:i:s"));
			$this->js()->redirect()->execute();
		}
	}
}