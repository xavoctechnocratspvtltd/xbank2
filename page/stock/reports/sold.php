<?php

class page_stock_reports_sold extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('dropdown','item')->setEmptyText('Please Select')->setModel('Stock_Item');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET LIST');

		$dead_stock=$this->add('Model_Stock_Transaction');
		$grid = $this->add('Grid');
		if($_GET['filter']){
			// Todo Filter 
			// if($_GET['from_date'])
			// 	$dead_stock->addCondition('created_at','>=',$_GET['from_date']);
			// if($_GET['to_date'])
			// 	$dead_stock->addCondition('created_at','<=',$_GET['to_date']);
		}
		else
			$dead_stock->addCondition('id',-1);
		
		$grid->setModel($dead_stock,array('item','qty','narration','rate','amount'));
		// $grid->addColumn('status','status');
		if($form->isSubmitted()){
			// todo sold Report item and all item wise
			// $grid->js()->reload(array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}