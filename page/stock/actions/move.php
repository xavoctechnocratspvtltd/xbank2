<?php

class page_stock_actions_move extends Page {
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
			// From Container
		$from_container_field = $form->addField('dropdown','from_container','Container')->validateNotNull()->setEmptyText('Please Select');
		$from_container_model = $this->add('Model_Stock_Container');
		$from_container_model->addCondition('branch_id',$this->api->currentBranch->id);
		$from_container_field->setModel($from_container_model);	
		$from_container_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);
			// From Row
		$from_row_field = $form->addField('dropdown','from_row','Row')->validateNotNull()->setEmptyText('Please Select');
		$from_row_model = $this->add('Model_Stock_Row');
		$from_row_model->addCondition('branch_id',$this->api->currentBranch->id);
		$from_row_field->setModel($from_row_model);	
		$from_row_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);
			// From Item
		$from_item_field = $form->addField('autocomplete/Basic','from_item','Item')->validateNotNull();//->setEmptyText('Please Select');
		$from_item_field->setModel('Stock_Item');	
		$from_item_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);
		
		$from_qty_field = $form->addField('Number','from_qty','Qty')->validateNotNull();
		$from_qty_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);		
		$from_narration_field = $form->addField('text','from_narration','Narration');
		$from_narration_field->js(true)->closest('div.atk-form-row')->appendTo($colleft);

		// Transfer Item TO
		$colright->add('View')->set('To')->setStyle(array('background-color'=>'#e1e1e1','padding'=>'5px'));
			// TO Container
		$to_container_field = $form->addField('dropdown','to_container','Container')->validateNotNull()->setEmptyText('Please Select');
		$to_container_model = $this->add('Model_Stock_Container');	
		$to_container_field->setModel($to_container_model);	
		$to_container_field->js(true)->closest('div.atk-form-row')->appendTo($colright);
			// To Row
		$to_row_field = $form->addField('dropdown','to_row','Row')->validateNotNull()->setEmptyText('Please Select');
		$to_row_model = $this->add('Model_Stock_Row');
			//Todo Load Row Accrding to Container selection
			// $to_row_model->addCondition('branch_id',$form['to_branch']);
		$to_row_field->setModel($to_row_model);	
		$to_row_field->js(true)->closest('div.atk-form-row')->appendTo($colright);	
		$to_narration_field = $form->addField('text','to_narration','Narration');
		$to_narration_field->js(true)->closest('div.atk-form-row')->appendTo($colright);
		
		$form->addSubmit('Move');

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

		$this->add('View_Info')->set(' Move ( Inner Branch ) Stock Transaction')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		$crud=$this->add('CRUD',array('allow_add'=>false,'allow_edit'=>false,'allow_delete'=>false));
		$transfer_transaction=$this->add('Model_Stock_Transaction');
		$transfer_transaction->addCondition('transaction_type','Move');
		$transfer_transaction->setOrder('created_at','desc');

		$crud->grid->addPaginator(10);

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
 
		$crud->setModel($transfer_transaction,array('item','qty','rate','created_at','narration'),array('item','qty','rate','created_at','narration'));

		if($form->isSubmitted()){
			
			$from_container_model = $this->add('Model_Stock_Container');
			$from_container_model->loadContainer($form['from_container']);

			$from_row_model = $this->add('Model_Stock_Row');
			if(!$from_row_model->loadRow($form['from_row'],$form['from_container']))
				$form->displayError('from_row','Row not Exist');

			$item_model = $this->add('Model_Stock_Item');
			$item_model->load($form['from_item']);
			
			if(!$item_model->isExistInContainerRow($from_container_model,$from_row_model))
				$form->displayError('from_item','Item not Exist in Selected Container or Row');

			$to_container_model = $this->add('Model_Stock_Container');
			$to_container_model->loadContainer($form['to_container']);

			$to_row_model = $this->add('Model_Stock_Row');
			// $to_row_model->loadRow($form['to_row'],$form['to_container']);
			if(!$to_row_model->loadRow($form['to_row'],$form['to_container']))
				$form->displayError('to_row','Row not Exist in Selected Container');

			// Entry in Transaction
			$transaction_model = $this->add('Model_Stock_Transaction');
			$transaction_model->move($from_container_model,$from_row_model,$item_model,$form['from_qty'],$form['from_narration'],$to_container_model,$to_row_model);
						
			$js = array($crud->js()->reload(),
					$form->js()->univ()->successMessage("Item ( ".$item_model['name']." ) From ( ".$from_container_model['name']."::".$from_row_model['name']." ) To ( ".$to_container_model['name']."::".$to_row_model['name']." ) with Qty (".$form['from_qty'].") Move Successfully" )
					);
			$form->js()->reload(null,$js)->execute();

		}


		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}


	}
}