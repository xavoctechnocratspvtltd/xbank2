<?php

class page_stock_actions_submit extends Page {
	function init(){
		parent::init();

		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');

		// Add Form 
		$form=$this->add('Form');
		$item_field=$form->addField('autocomplete/Basic','item');//->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');
		$staff_field=$form->addField('dropdown','staff')->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Staff');
		$agent_field=$form->addField('dropdown','agent')->setEmptyText('Please Select');
		$agent_field->setModel('Stock_Agent');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('Stock_Dealer');
		$form->addField('line','qty');
		$form->addField('text','narration');
		$form->addSubmit('Issue');

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

		// $crud=$this->add('crud');
		$crud=$this->add('CRUD',array('allow_add'=>false));
		$submit_transaction=$this->add('Model_Stock_Transaction');
		$submit_transaction->addCondition('transaction_type','Submit');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('item');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($_GET['item'])
				$submit_transaction->addCondition('item_id',$_GET['item']);
			if($_GET['from_date'])
				$submit_transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$submit_transaction->addCondition('created_at','<=',$_GET['to_date']);
		}

		$crud->setModel($submit_transaction,array('branch','staff','agent','dealer','item','qty','submit_date','narration'));

		if($form->isSubmitted()){
			
			$staff=$this->add('Model_Stock_Staff')->tryLoad($form['staff']);
			$agent=$this->add('Model_Stock_Agent')->tryLoad($form['agent']);
			$dealer=$this->add('Model_Stock_Dealer')->tryLoad($form['dealer']);

			$item=$this->add('Model_Stock_Item')->load($form['item']);
			if(!$item->canSubmit($form['qty'],$staff,$agent,$dealer))
				$form->displayError('qty',"This Item is not issue in Such qty");

			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->submit($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			
			$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
			$criq_model->addStockInGeneral($item,$form['qty']);

			$js=array($crud->grid->js()->reload(),
					$form->js()->univ()->successMessage("Item ( ".$item['name']." ) submitted Succesfully")
					);
			$form->js()->reload(null,$js)->execute();
		}

		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}

	}
}