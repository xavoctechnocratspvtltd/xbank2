<?php

class page_stock_actions_usedsubmit extends Page {
	function init(){
		parent::init();

		$issue_item_model = $this->add('Model_Stock_Item')->addCondition('is_issueable',true);
		
		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');
		$form=$this->add('Form');
		$item_field=$form->addField('autocomplete/Basic','item')->validateNotNull();
		$item_field->setModel($issue_item_model);

		$staff_field=$form->addField('dropdown','staff')->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Staff');
		$agent_field=$form->addField('dropdown','agent')->setEmptyText('Please Select');
		$agent_field->setModel('Stock_Agent');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('Stock_Dealer');

		$form->addField('Number','qty')->validateNotNull();
		$form->addField('text','narration');

		$form->addSubmit('Used Submit');

		$form_search=$this->add('Form');
		$item_field=$form_search->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel($issue_item_model);
		$form_search->addField('DatePicker','from_date');
		$form_search->addField('DatePicker','to_date');
		$form_search->addSubmit('GET LIST');

		$search_btn->js('click',array($form_search->js()->toggle(),$form->js()->hide()));
		$add_btn->js('click',array($form->js()->toggle(),$form_search->js()->hide()));
		$form_search->js(true)->hide();
		$form->js(true)->hide();

		$this->add('View_Info')->set('Used Stock Transation')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		$crud=$this->add('CRUD',array('allow_add'=>false,'allow_del'=>true));

		$used_transaction=$this->add('Model_Stock_Transaction');
		$used_transaction->addCondition('transaction_type','UsedSubmit');
		$used_transaction->addCondition('branch_id',$this->api->currentBranch->id);
		$used_transaction->setOrder('created_at','desc');
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('item');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($_GET['item'])
				$used_transaction->addCondition('item_id',$_GET['item']);
			if($_GET['from_date'])
				$used_transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$used_transaction->addCondition('created_at','<=',$_GET['to_date']);
		}

		$crud->setModel($used_transaction,array('item','qty','amount','narration','created_at','member','transaction_type'));

		if($form->isSubmitted()){
			
			$item=$this->add('Model_Stock_Item')->load($form['item']);

			$staff=$this->add('Model_Stock_Staff')->tryLoad($form['staff']);
			$agent=$this->add('Model_Stock_Agent')->tryLoad($form['agent']);
			$dealer=$this->add('Model_Stock_Dealer')->tryLoad($form['dealer']);
			
			if(!$item->canSubmit($form['qty'],null,$staff,$agent,$dealer))
				$form->displayError('qty',"This Item is not issue to ".$staff['name'].$agent['name'].$dealer['name']." in Such qty");	

			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->usedSubmit($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			
			$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
			$criq_model->addStockInUsedDefault($item,$form['qty']);

			$js = array($crud->grid->js()->reload(),
					$form->js()->univ()->successMessage("Item ( ".$item['name']." ) with ".$staff['name'].$agent['name'].$dealer['name']." UsedSubmit Successfully")
					);
			$form->js(null,$js)->reload()->execute();
		}

		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}

	}
}