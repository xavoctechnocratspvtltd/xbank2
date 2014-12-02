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
		$msg = "Staff Report";
		$item_name = "All";
		
		if($_GET['filter']){
			$staff_model = $this->add('Model_Stock_Staff')->load($_GET['staff']);
			if($_GET['item']){
				$item_model = $this->add('Model_Stock_Item')->load($_GET['item']);
				$item_name = $item_model['name'];
			}

			$msg = "Staff ( ".$staff_model['name']." ) Item ( ".$item_name." ) From Date: ".$_GET['from_date']." To Date: ".$_GET['to_date'];
			$v->add('View_Info')->set($msg)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
			$view_consume=$v->add('View_StockMember_Report',array('member'=>$_GET['staff'],'item'=>$_GET['item'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));
		}
		
		if($form->isSubmitted()){
			$v->js()->reload(array('staff'=>$form['staff'],'item'=>$form['item'],'from_date'=>$form['from_date']?:'1970-01-01','to_date'=>$form['to_date']?:$this->api->now,'filter'=>1))->execute();		
			//$grid->js()->reload(array('member'=>$form['staff'],'item'=>$form['item'],'from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'filter'=>1))->execute();	
		}
	
	}

}