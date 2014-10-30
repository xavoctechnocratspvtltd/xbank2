<?php

class page_stock_ledger_item extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		// $form->addField('CheckBox','all_item');
		// $form->addField('CheckBox','include_dead');
		$form->addSubmit('GET');
		
		if($form->isSubmitted()){			
			// Todo Item item Ledger 
		}

	}
}