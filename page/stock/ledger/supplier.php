<?php

class page_stock_ledger_supplier extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');

		$supplier_field=$form->addField('dropdown','supplier')->setEmptyText('Please Select');
		$supplier_field->setModel('Stock_Supplier');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET');

		$transaction=$this->add('Model_Stock_Transaction');

		$grid=$this->add('Grid_AccountsBase');
		
		if($_GET['filter']){
			//Todo if filter is available 
		}else
			$transaction->addCondition('id',-1);
		
		$grid->setModel($transaction,array('item','qty','created_at'));	

		if($form->isSubmitted()){
			$grid->js()->reload(array('supplier'=>$form['supplier'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}