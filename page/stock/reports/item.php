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
		$grid = $this->add('Grid_AccountsBase');
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

		if($form->isSubmitted()){
			$grid->js()->reload(array('container'=>$form['container'],'item'=>$form['item'],'filter'=>1))->execute();
		}

		$grid->removeColumn('branch');
	}
}