<?php

class page_stock_actions_return extends Page {
	function init(){
		parent::init();
 
		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');

		$form=$this->add('Form');
		$supplier_field=$form->addField('dropdown','supplier')->validateNotNull()->setEmptyText('Please Select');
		$supplier_model=$this->add('Model_Stock_Supplier');
		$supplier_model->addCondition('is_active',true);
		$supplier_field->setModel($supplier_model);

		$item_field=$form->addField('autocomplete/Basic','item')->validateNotNull();//->setEmptyText('Please Select');
		$item_model = $this->add('Model_Stock_Item');
		$item_model->addCondition('is_active',true);
		$item_field->setModel($item_model);

		$form->addField('Number','qty')->validateNotNull();
		$form->addField('Number','rate')->validateNotNull();
		$form->addField('text','narration');
		$form->addSubmit('Purchase Return');

		$form_search=$this->add('Form');
		$item_field=$form_search->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');
		$form_search->addField('DatePicker','from_date');
		$form_search->addField('DatePicker','to_date');
		$form_search->addSubmit('GET LIST');

		$search_btn->js('click',array($form_search->js()->toggle(),$form->js()->hide()));
		$add_btn->js('click',array($form->js()->toggle(),$form_search->js()->hide()));
		$form_search->js(true)->hide();
		$form->js(true)->hide();

		// $crud=$this->add('crud');
		$this->add('View_Info')->set('Purchase Return Stock Transaction')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		
		if($this->app->currentStaff->isSuper() || $this->app->currentStaff->isCEO()){
			$crud = $this->add('CRUD');
		}else
			$crud=$this->add('CRUD',array('allow_add'=>false,'allow_edit'=>false,'allow_del'=>false));
		
		$purchase_return_transaction=$this->add('Model_Stock_Transaction');
		$purchase_return_transaction->addCondition('transaction_type','PurchaseReturn');
		$purchase_return_transaction->addCondition('branch_id',$this->api->currentBranch->id);		
		$purchase_return_transaction->setOrder('created_at','desc');
		$crud->grid->addPaginator(100);		
		// DO Search Filter		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('item');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($_GET['item'])
				$purchase_return_transaction->addCondition('item_id',$_GET['item']);
			if($_GET['from_date'])
				$purchase_return_transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$purchase_return_transaction->addCondition('created_at','<=',$_GET['to_date']);
		}
		// end Search Filter

		$crud->setModel($purchase_return_transaction,array('branch','item','member','qty','rate','created_at','narration'),array('branch','item','member','qty','rate','created_at','narration'));

		if($form->isSubmitted()){
			$supplier=$this->add('Model_Stock_Supplier');
			$supplier->load($form['supplier']);
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->purchaseReturn($supplier,$item,$form['qty'],$form['rate'],$form['narration']);
			$form->js()->reload(null,$crud->js()->reload())->execute();		
		}

		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}
	}
}