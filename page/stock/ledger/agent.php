<?php	
class page_stock_ledger_Agent extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');
		
		$staff_field=$form->addField('dropdown','agent')->validateNotNull()->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET');
		$v=$this->add('View');
		
		$tab = $v->add('Tabs');
		$scl_tab = $tab->addTab('Agent Consume Leadger');
		$sil_tab = $tab->addTab('Agent Issue Leadger');
		$sfl_tab = $tab->addTab('Agent FixedAssets Leadger');

		$str = "Agent Leadger";
		if($_GET['filter']){
			$staff_model = $this->add('Model_Stock_Agent')->load($_GET['agent']);
			$item_model = $this->add('Model_Stock_Item');
			$item_model->addCondition('id',$_GET['item']);
			$item_model->tryLoadAny();
			$item_name = "All";
			if($item_model->loaded())
				$item_name = $item_model['name'];
			$str = "Agent ( ".$staff_model['name']." ) Ledger on Item ( ".$item_name." ) From Date: ".$_GET['from_date']." To date: ".$_GET['to_date'];
		}

		$scl_tab->add('View_Info')->set($str)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		$view_consume=$scl_tab->add('View_StockMember_Consume',array('member'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Agent'));
		
		$sil_tab->add('View_Info')->set($str)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		$view_issue=$sil_tab->add('View_StockMember_Issue',array('item'=>$_GET['item'],'member'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Agent'));

		$sfl_tab->add('View_Info')->set($str)->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		$view_fixed=$sfl_tab->add('View_StockMember_FixedAssets',array('member'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Agent'));
		
		if($form->isSubmitted()){
			$v->js()->reload(array('agent'=>$form['agent'],'item'=>$form['item'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}


	}
}