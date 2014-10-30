<?php

class page_stock_reports_sold extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET LIST');

		$grid=$this->add('Grid_AccountsBase');
		$dead_stock=$this->add('Model_Stock_Transaction');
		$dead_stock->addCondition('transaction_type',array('Sold'));
		// $grid->addMethod('format_status',function($grid,$field){
		// 	if($grid->model['transaction_type']=='Dead')
		// 			 $grid->current_row[$field]='Hold';
		// 		else
		// 			 $grid->current_row[$field]='Sold';
		// });


		if($_GET['filter']){
			if($_GET['from_date'])
				$dead_stock->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$dead_stock->addCondition('created_at','<=',$_GET['to_date']);
		}
		else
			$dead_stock->addCondition('id',-1);
		$grid->setModel($dead_stock,array('item','qty','narration','rate','amount'));
		// $grid->addColumn('status','status');
		$grid->addSno();
		if($form->isSubmitted()){
			$grid->js()->reload(array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}
	}
}