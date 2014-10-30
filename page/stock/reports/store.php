<?php

class page_stock_reports_store extends Page {
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$container_field=$form->addField('dropdown','container')->setEmptyText('All');
		$container_field->setModel('Stock_Container');

		$form->addSubmit('GET LIST');

		if($form->isSubmitted()){
			
		}
	
	}

}