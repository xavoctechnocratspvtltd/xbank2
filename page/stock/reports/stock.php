<?php

class page_stock_reports_stock extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','to_date','As On');
		$form->addSubmit('GET LIST');

		if($form->isSubmitted()){
			// todo 
		}	

	}
}