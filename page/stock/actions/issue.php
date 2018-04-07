<?php

class page_stock_actions_issue extends Page {
	function init(){
		parent::init();

		$search_btn=$this->add('Button')->set('Search');
		$add_btn=$this->add('Button')->set('Add');

		// Issue Form
		$form=$this->add('Form');
		$container_field = $form->addField('dropdown','container')->validateNotNull()->setEmptyText('Please Select');		
		$container_model = $this->add('Model_Stock_Container');
		$container_model->addCondition('name','<>','General');
		$container_model->addCondition('name','<>','Dead');
		$container_field->setModel($container_model);
		
		$container_field->js('change',$form->js()->atk4_form('reloadField','row',array($this->api->url(),'container'=>$container_field->js()->val())));

		$row_field = $form->addField('dropdown','row')->validateNotNull()->setEmptyText('Please Select');
		$row_model = $this->add('Model_Stock_Row');
		$row_model->addCondition('name','<>','General');
		$row_model->addCondition('name','<>','Dead');
		if($_GET['container']){
			$container = $this->api->stickyGET('container');
			$row_model->addCondition('container_id',$container);
		}
		$row_field->setModel($row_model);

		$item_field=$form->addField('autocomplete/Basic','item')->validateNotNull();//->setEmptyText('Please Select')->validateNotNull();
		$item_model=$this->add('Model_Stock_Item');
		// $item_model->addCondition('is_issueable',true);
		$item_model->addCondition(
    		$item_model->dsql()->orExpr()
     			->where('is_issueable',true)
     			->where('is_fixedassets',true)
     		);
		// $item_model->addCondition('is_fixedassets',true);
		$item_field->setModel($item_model);
		$staff_field=$form->addField('dropdown','staff')->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Staff');
		$agent_field=$form->addField('dropdown','agent')->setEmptyText('Please Select');
		$agent_field->setModel('Stock_Agent');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('Stock_Dealer');
		$form->addField('Number','qty')->validateNotNull();
		$form->addField('text','narration');
		$form->addSubmit('Issue');

		// Search form
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

		$this->add('View_Info')->set('Issue Stock Transation')->setStyle(array('padding'=>'2px','margin'=>'5px 0 5px 0'));
		if($this->app->currentStaff->isSuper() || $this->app->currentStaff->isCEO()){
			$crud = $this->add('CRUD');
		}else
			$crud=$this->add('CRUD',array('allow_add'=>false,'allow_edit'=>false,'allow_del'=>false));
		if($crud->grid){
			$crud->grid->addPaginator(100);
		}
		
		$issue_transaction=$this->add('Model_Stock_Transaction');
		$issue_transaction->addCondition('transaction_type',array('Issue'));
		$issue_transaction->addCondition('branch_id',$this->api->currentBranch->id);
		$issue_transaction->setOrder('created_at','desc');

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

		$crud->setModel($issue_transaction,array('item','qty','amount','narration','created_at','member','transaction_type'));
		if($form->isSubmitted()){

			$item=$this->add('Model_Stock_Item')->load($form['item']);
			$container=$this->add('Model_Stock_Container')->tryLoad($form['container']);
			$row=$this->add('Model_Stock_Row')->tryLoad($form['row']);
			
			$criq_model = $this->add('Model_Stock_ContainerRowItemQty');
			$old_qty = $criq_model->getItemQty($container,$row,$item);
			if($form['qty'] > $old_qty){
				throw $this->exception("This ( ".$item['name'] ." ) Is not availeble in such Qty ( ".$form['qty']." )", 'ValidityCheck')->setField('qty');
			}

			$staff=$this->add('Model_Stock_Staff')->tryLoad($form['staff']);
			$agent=$this->add('Model_Stock_Agent')->tryLoad($form['agent']);
			$dealer=$this->add('Model_Stock_Dealer')->tryLoad($form['dealer']);
			$transaction=$this->add('Model_Stock_Transaction');

			if($item['is_issueable'] or $item['is_fixedassets']){
				$transaction->issue($item,$form['qty'],$form['narration'],$staff,$agent,$dealer,$container,$row);
				$criq_model->removeStock($container,$row,$item,$form['qty']);
			}
			//todo item removed from its current location	
			
			$js = array($crud->grid->js()->reload(),
					$form->js()->univ()->successMessage("item ( ".$item['name']." ) Issue to ( ".$staff['name'].$agent['name'].$dealer['name']." ) Qty ( ".$form['qty']." )")
				);
			$form->js(null,$js)->reload()->execute();

		}

		if($form_search->isSubmitted()){
			$crud->grid->js()->reload(array('item'=>$form_search['item'],'from_date'=>$form_search['from_date']?:0,'to_date'=>$form_search['to_date']?:0,'filter'=>1))->execute();
		}
	}
}