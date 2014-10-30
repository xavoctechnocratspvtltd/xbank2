<?php

class page_stock_reports_genral extends Page{
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','type')->setValueList(array('Purchase','Issue','Consume','Submit','PurchaseReturn','Dead','Transfer','Openning','Sold'));
		$staff_field=$form->addField('autocomplete/Basic','staff');
		$staff_field->setModel('Stock_Staff');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Stock_Agent');
		$dealer_field=$form->addField('autocomplete/Basic','dealer');
		$dealer_field->setModel('Stock_Dealer');
		$form->addSubmit('GET LIST');

		$grid=$this->add('Grid');
		$transaction=$this->add('Model_Stock_Transaction');

		if($_GET['filter']){
			// todo filter
		}

		$grid->setModel($transaction);

		if($form->isSubmitted()){
			// todo general report 
			//$grid->js()->reload(array('filter'=>1,'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'staff'=>$form['staff'],'agent'=>$form['agent'],'dealer'=>$form['dealer'],'type'=>$form['type']))->execute();
		}

	}
}