<?php

class page_stock_reports_dead extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('dropdown','item')->setEmptyText('Please Select')->setModel('Stock_Item');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET LIST');

		if($form->isSubmitted()){
			$grid->js()->reload(array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}
	}
}