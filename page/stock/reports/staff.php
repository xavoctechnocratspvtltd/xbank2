<?php

class page_stock_reports_staff extends Page {
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$staff_field=$form->addField('dropdown','staff')->validateNotNull()->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Staff');
		
		$item_field=$form->addField('dropdown','item')->setEmptyText('All');
		$item_model = $this->add('Model_Stock_Item');
		$item_field->setModel($item_model);
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET LIST');

		$v=$this->add('View');
		if($_GET['filter'])
			$view_consume=$v->add('View_StockMember_Report',array('member'=>$_GET['staff'],'item'=>$_GET['item'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));

		if($form->isSubmitted()){
			$v->js()->reload(array('staff'=>$form['staff'],'item'=>$form['item'],'from_date'=>$form['from_date']?:'1970-01-01','to_date'=>$form['to_date']?:$this->api->now,'filter'=>1))->execute();		
			//$grid->js()->reload(array('member'=>$form['staff'],'item'=>$form['item'],'from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'filter'=>1))->execute();	
		}
	
	}

}