<?php

class page_stock_reports_itemtransaction extends Page{

	function init(){
		parent::init();

		$form = $this->add('Form');
		$item_field = $form->addField('dropdown','item')->validateNotNull()->setEmptyText('Please Select');
		$transaction_field = $form->addField('dropdown','transaction')->setValueList(array('Issue'=>'Issue','Purchase'=>'Purchase','Consume'=>'Consume','Submit'=>'Submit','PurchaseReturn'=>'PurchaseReturn','DeadSubmit'=>'DeadSubmit','Transfer'=>'Transfer','Sold'=>'Sold','DeadSold'=>'DeadSold'))->setEmptyText('Please Select');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$item = $this->add('Model_Stock_Item');
		$item_field->setModel($item);

		$form->addSubmit('GET LIST');

		if($form->isSubmitted()){

		}
	}
}