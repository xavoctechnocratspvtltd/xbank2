<?php

class page_stock_actions_transfer extends Page {
	function init(){
		parent::init();

		
		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');
		$form=$this->add('Form');
		$branch_field=$form->addField('dropdown','branch')->setEmptyText('Please Select');
		$branch_model=$this->add('Model_Branch');
		$branch_model->addCondition('id','<>',$this->api->currentBranch->id);
		$branch_field->setModel($branch_model);	
		$item_field=$form->addField('autocomplete/Basic','item');//->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');	
		$form->addField('line','qty');
		$rate_field=$form->addField('line','rate');
		$form->addField('text','narration');
		$form->addSubmit('Transfer');

		$this->js(true)->_load('avgrate13');
		// $item_field->other_field->js('change',$this->js()->univ()->test());
		$item_field->other_field->js('change',$this->js()->univ()->avgrate($item_field,$rate_field));

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



		// $grid=$this->add('Grid');
		$crud=$this->add('CRUD',array('allow_add'=>false));
		$transfer_transaction=$this->add('Model_Stock_Transaction');
		$transfer_transaction->addCondition('transaction_type','Transfer');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('item');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($_GET['item'])
				$transfer_transaction->addCondition('item_id',$_GET['item']);
			if($_GET['from_date'])
				$transfer_transaction->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transfer_transaction->addCondition('created_at','<=',$_GET['to_date']);
		}

		$crud->setModel($transfer_transaction,array('item','to_branch','qty','rate','created_at','narration'));

		if($form->isSubmitted()){

			$transaction->transfer($item,$branch,$form['qty'],$form['rate'],$form['narration']);
			$form->js()->reload(null,$grid->js()->reload())->execute();
		}


		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}
	}


}