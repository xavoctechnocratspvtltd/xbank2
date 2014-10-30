<?php

class page_stock_reports_purchase extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('dropdown','item')->setEmptyText('Please Select')->setModel('Stock_Item');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET LIST');

		if($form->isSubmitted()){
			
		}

	}
}