<?php

class page_stock_actions_issue extends Page {
	function init(){
		parent::init();

		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');

		$form=$this->add('Form');
		$item_field=$form->addField('autocomplete/Basic','item');//->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');

		$staff_field=$form->addField('dropdown','staff')->setEmptyText('Please Select');
		$staff_field->setModel('Staff');

		$agent_field=$form->addField('dropdown','agent')->setEmptyText('Please Select');
		$agent_field->setModel('Agent');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('Dealer');

		$form->addField('line','qty');
		$form->addField('text','narration');
		$form->addSubmit('Issue');


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
		$issue_transaction=$this->add('Model_Stock_Transaction');
		$issue_transaction->addCondition('transaction_type',array('Issue','Consume'));
		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('item');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($_GET['item'])
				$issue_transaction->addCondition('item_id',$_GET['item']);
			if($_GET['from_date'])
				$issue_transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$issue_transaction->addCondition('created_at','<=',$_GET['to_date']);
		}

		$crud->setModel($issue_transaction,array('branch','item','staff','qty','issue_date','narration','transaction_type'));

		if($form->isSubmitted()){
			$item=$this->add('Model_Stock_Item')->load($form['item']);
			$staff=$this->add('Model_Staff')->tryLoad($form['staff']);
			$agent=$this->add('Model_Agent')->tryLoad($form['agent']);
			$dealer=$this->add('Model_Dealer')->tryLoad($form['dealer']);
			$transaction=$this->add('Model_Stock_Transaction');

			if($item['is_consumable'])
					$transaction->consume($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			if($item['is_issueable'])
					$transaction->issue($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			$form->js(null,$crud->grid->js()->reload())->reload()->execute();

		}

		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}
	}
}