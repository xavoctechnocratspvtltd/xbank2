<?php

class page_utility_setdate extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','date')->set($this->api->today)->validateNotNull();
		$form->addSubmit('Change');

		if($form->isSubmitted()){
			$this->api->setDate($form['date']);
			$form->js()->reload()->execute();
		}
	}
}