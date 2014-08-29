<?php

class page_stock_actions_purchase extends Page {
	function init(){
		parent::init();

		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');
		$form=$this->add('Form');
		$party_field=$form->addField('dropdown','party')->setEmptyText('Please Select');
		$party_field->setModel('Stock_Party');	
		$item_field=$form->addField('autocomplete/Basic','item');//->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');	
		$form->addField('line','qty');
		$form->addField('line','rate');
		$form->addField('text','narration');
		$form->addSubmit('Purchase');

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


		$crud=$this->add('CRUD',array('allow_add'=>false));
		$purchase_transaction=$this->add('Model_Stock_Transaction');
		$purchase_transaction->addCondition('transaction_type','Purchase');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('item');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($_GET['item'])
				$purchase_transaction->addCondition('item_id',$_GET['item']);
			if($_GET['from_date'])
				$purchase_transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$purchase_transaction->addCondition('created_at','<=',$_GET['to_date']);
		}
		$crud->setModel($purchase_transaction,array('item','party','branch','qty','rate','narration','created_at'));

		if($form->isSubmitted()){
			$party=$this->add('Model_Stock_Party');
			$party->load($form['party']);
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->purchase($party,$item,$form['qty'],$form['rate'],$form['narration']);
			$form->js()->reload(null,$crud->grid->js()->reload())->execute();
		}

		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}
	}


}