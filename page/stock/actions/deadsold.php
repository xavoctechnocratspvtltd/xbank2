<?php

class page_stock_actions_deadsold extends Page {
	function init(){
		parent::init();
		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');

		// Sold stock Form
		$form=$this->add('Form');
		$item_field=$form->addField('autocomplete/Basic','item')->validateNotNull();//->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');
		$form->addField('Number','qty')->validateNotNull();
		$form->addField('line','rate')->validateNotNull();
		// $form->addField('line','amount');
		$form->addField('text','narration');
		$form->addSubmit('Sold');

		//Search Form 
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

		$this->add('View_Info')->set('Dead Item Sold - Stock Transation')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		if($this->app->currentStaff->isSuper() || $this->app->currentStaff->isCEO()){
			$crud = $this->add('CRUD');
		}else
			$crud=$this->add('CRUD',array('allow_add'=>false,'allow_edit'=>false,'allow_del'=>false));
			
		$sold_transaction=$this->add('Model_Stock_Transaction');
		$sold_transaction->addCondition('transaction_type','DeadSold');
		$sold_transaction->addCondition('branch_id',$this->api->currentBranch->id);
		$sold_transaction->setOrder('created_at','desc');
		if($crud->grid){
			$crud->grid->addPaginator(100);
		}
		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('item');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($_GET['item'])
				$sold_transaction->addCondition('item_id',$_GET['item']);
			if($_GET['from_date'])
				$sold_transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$sold_transaction->addCondition('created_at','<=',$_GET['to_date']);
		}

		$crud->setModel($sold_transaction,array('item','qty','amount','narration','created_at','member','transaction_type'));

		if($form->isSubmitted()){

			$item=$this->add('Model_Stock_Item')->load($form['item']);
			
			if(!$item->getDeadQty($form['qty']))
				$form->displayError('qty',"DeadSubmit Item is not  in Such qty");
			
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->deadSold($item,$form['qty'],$form['rate'],$form['narration']);

			$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
			$criq_model->destroyStockFromDead($item,$form['qty']);

			$js = array($crud->grid->js()->reload(),
					$form->js()->univ()->successMessage("Dead Item ( ".$item['name']." ) with qty ( ".$form['qty']." ) Sold Successfully")			
					);
			$form->js(null,$js)->reload()->execute();
		}

		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}

	}
}