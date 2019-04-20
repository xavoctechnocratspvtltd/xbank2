<?php

class page_stocknew_ledgers extends Page {
	
	function page_index(){

		// $tabs = $this->add('Tabs');
		// $tabs->addTabURL($this->app->url('./item'),'Item');
		$this->add('Controller_Acl',['default_view'=>false]);
		$this->page_item();
	}

	function page_item(){

		$form = $this->add('Form');
		$form->addField('DropDown','branch')->setEmptyText('All')->setModel('Branch')->addCondition('id',$this->app->current_branch->id);
		$form->addField('DropDown','container')->setEmptyText('All')->setModel('StockNew_Container')->addCondition('branch_id',$this->app->current_branch->id);
		$form->addField('DropDown','container_row')->setEmptyText('All')->setModel('StockNew_ContainerRow')->addCondition('branch_id',$this->app->current_branch->id);
		$form->addField('autocomplete/Basic','member')->setModel('StockNew_Member');
		$form->addField('autocomplete/Basic','item')->setModel('StockNew_Item');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('Filter');
		$form->add('Controller_StockNewFieldFilter',['branch_field'=>'branch','container_field'=>'container','container_row_field'=>'container_row']);

		$model = $this->add('Model_StockNew_Transaction');


		if($branch = $this->app->stickyGET('branch')){
			$model->addCondition([['from_branch_id',$branch],['to_branch_id',$branch]]);
		}

		if($container = $this->app->stickyGET('container')){
			$model->addCondition([['from_container_id',$container],['to_container_id',$container]]);
		}

		if($container_row = $this->app->stickyGET('container_row')){
			$model->addCondition([['from_container_row_id',$container_row],['to_container_row_id',$container_row]]);
		}

		if($member = $this->app->stickyGET('member')){
			$model->addCondition([['from_member_id',$member],['to_member_id',$member]]);
		}


		if($item = $this->app->stickyGET('item')){
			$model->addCondition('item_id',$item);
		}

		if($from_date= $this->app->stickyGET('from_date')){
			$model->addCondition('created_at','>=',$from_date);
		}

		if($to_date= $this->app->stickyGET('to_date')){
			$model->addCondition('created_at','<',$this->app->nextDate($to_date));
		}

		if(!$this->app->stickyGET('filter')){
			$model->addCondition('id',-1);
		}

		$grid = $this->add('Grid');
		$grid->setModel($model);

		$grid->addPaginator(100);

		if($form->isSubmitted()){
			$grid->js()->reload([
				'filter'=>1,
				'branch'=>$form['branch']?:0,
				'container'=>$form['container']?:0,
				'container_row'=>$form['container_row']?:0,
				'member'=>$form['member']?:0,
				'item'=>$form['item'],
				'from_date'=>$form['from_date']?:0,
				'to_date'=>$form['to_date']?:0
			])->execute();
		}
	}
}