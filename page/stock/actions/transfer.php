<?php

class page_stock_actions_transfer extends Page {
	function init(){
		parent::init();

		
		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');
		$form=$this->add('Form');

		$col = $form->add('Columns');
		$colleft = $col->addColumn(5);
		$colmid = $col->addColumn(2);
		$colright = $col->addColumn(5);

		// Transfer Item from 
		$colleft->add('View')->set('From')->setStyle(array('background-color'=>'#f12039','padding'=>'5px'));
		$from_branch_field = $form->addField('dropdown','from_branch','Branch')->setEmptyText('Please Select');
		$from_branch_model = $this->add('Model_Branch');
		$from_branch_model->addCondition('id',$this->api->currentBranch->id);
		$from_branch_field->setModel($from_branch_model);	
		$from_branch_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);
			// From Container
		$from_container_field = $form->addField('dropdown','from_container','Container')->setEmptyText('Please Select');
		$from_container_model = $this->add('Model_Stock_Container');
		$from_container_model->addCondition('branch_id',$this->api->currentBranch->id);
		$from_container_field->setModel($from_container_model);	
		$from_container_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);
			// From Row
		$from_row_field = $form->addField('dropdown','from_row','Row')->setEmptyText('Please Select');
		$from_row_model = $this->add('Model_Stock_Row');
		$from_row_model->addCondition('branch_id',$this->api->currentBranch->id);
		$from_row_field->setModel($from_row_model);	
		$from_row_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);
			// From Item
		$from_item_field = $form->addField('autocomplete/Basic','from_item','Item');//->setEmptyText('Please Select');
		$from_item_field->setModel('Stock_Item');	
		$from_item_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);		
		$from_qty_field = $form->addField('line','from_qty','Qty');
		$from_qty_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);		
		$from_narration_field = $form->addField('text','from_narration','Narration');
		$from_narration_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);

		// Transfer Item TO
		$colright->add('View')->set('To')->setStyle(array('background-color'=>'#e1e1e1','padding'=>'5px'));
		$to_branch_field = $form->addField('dropdown','to_branch','Branch')->setEmptyText('Please Select');
		$to_branch_model = $this->add('Model_Branch');
		$to_branch_model->addCondition('id','<>',$this->api->currentBranch->id);		
		$to_branch_field->setModel($to_branch_model);
		$to_branch_field->js(true)->closest('div.atk-form-row')->appendTo($colright);		
		
		$form->addSubmit('Transfer');

		// $this->js(true)->_load('avgrate13');
		// $from_item_field->other_field->js('change',$this->js()->univ()->test());
		// $from_item_field->other_field->js('change',$this->js()->univ()->avgrate($item_field,$rate_field));

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
			// todo Transfer Stock item between same and other branch
		}


		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}


	}
}