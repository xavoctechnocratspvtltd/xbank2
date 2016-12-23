<?php

class page_stock_reports_item extends Page{
	function init(){
		parent::init();

		$form = $this->add('Form');
		$container_field = $form->addField('dropdown','container')->setEmptyText('Please Select');
		$item_field = $form->addField('dropdown','item')->setEmptyText('Please Select');
		
		$container = $this->add('Model_Stock_Container');
		$container_field->setModel($container);	
		$item = $this->add('Model_Stock_Item');	
		$item_field->setModel($item);

		$form->addSubmit('GET LIST');

		$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
		$criq_model->addCondition('branch_id',$this->api->currentBranch->id);
		
		$v = $this->add('View');
		$item_name = "All";
		$msg = "Item Wherehouse Report";
		$msg_view = $v->add('View_Info')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		
		$grid = $v->add('Grid_AccountsBase');
		$grid->addSno();

		if($_GET['filter']){
			if($_GET['container'])
				$criq_model->addCondition('container_id',$_GET['container']);
			if($_GET['item'])
				$criq_model->addCondition('item_id',$_GET['item']);
			$criq_model->tryLoadAny();
			$grid->setModel($criq_model);
		}else
			$grid->setModel($criq_model);

		$msg_view->set($msg);	
		if($form->isSubmitted()){
			$grid->js()->reload(array('container'=>$form['container'],'item'=>$form['item'],'filter'=>1))->execute();
		}

		$grid->removeColumn('branch');
	}
}