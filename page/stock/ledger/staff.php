<?php	
class page_stock_ledger_staff extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');
		$staff_field=$form->addField('dropdown','staff')->validateNotNull()->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Staff');
 
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET');
		$v=$this->add('View');
		
		$tab = $v->add('Tabs');
		$scl_tab = $tab->addTab('Staff Consume Leadger');
		$sil_tab = $tab->addTab('Staff Issue Leadger');
		$sfl_tab = $tab->addTab('Staff FixedAssets Leadger');

		$str = "Staff Leadger";
		if($_GET['filter']){
			
			$this->api->stickyGET('staff');
			$staff_model = $this->add('Model_Stock_Staff')->load($_GET['staff']);
			$item_model = $this->add('Model_Stock_Item');
			$item_model->addCondition('id',$_GET['item']);
			$item_model->tryLoadAny();
			$item_name = "All";
			if($item_model->loaded())
				$item_name = $item_model['name'];
			$str = "Staff ( ".$staff_model['name']." ) Ledger on Item ( ".$item_name." ) From Date: ".$_GET['from_date']." To date: ".$_GET['to_date'];

			$scl_tab->add('View_Info')->set($str)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
			$view_consume=$scl_tab->add('View_StockMember_Consume',array('item'=>$_GET['item'],'member'=>$_GET['staff'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));
			
			$sil_tab->add('View_Info')->set($str)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
			$view_issue=$sil_tab->add('View_StockMember_Issue',array('item'=>$_GET['item'],'member'=>$_GET['staff'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));

			$sfl_tab->add('View_Info')->set($str)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
			$view_fixed=$sfl_tab->add('View_StockMember_FixedAssets',array('item'=>$_GET['item'],'member'=>$_GET['staff'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));
		}

		// throw new \Exception("Filter".$_GET['staff'], 1);

		if($form->isSubmitted()){			
			$v->js()->reload(array('item'=>$form['item'],'staff'=>$form['staff'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}