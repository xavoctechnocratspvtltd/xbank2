<?php

class page_stocknew_reports extends Page {
	
	function page_index(){
		
		$this->add('Controller_Acl',['default_view'=>false]);

		$tabs = $this->add('Tabs');
		$overall_stock_report_tab = $tabs->addTabURL($this->app->url('./overall'),'Over All Stock Report');
		$stock_distribution_report_tab = $tabs->addTabURL($this->app->url('./distribution'),'Stock Distribution Report');
	}

	function page_overall(){

		$this->add('H3')->set('Stock Report');

		$form = $this->add('Form');
		$branch = $this->add('Model_Branch');
		$container_model = $this->add('Model_StockNew_Container');
		$container_row_model = $this->add('Model_StockNew_ContainerRow');

		if($this->app->current_branch->id){
			$branch->addCondition('id',$this->app->current_branch->id);
			$container_model->addCondition('branch_id',$this->app->current_branch->id);
			$container_row_model->addCondition('branch_id',$this->app->current_branch->id);
		}
			
		$form->addField('DropDown','branch')->setEmptyText('All')->setModel($branch);
		$form->addField('DropDown','container')->setEmptyText('All')->setModel($container_model);
		$form->addField('DropDown','container_row')->setEmptyText('All')->setModel($container_row_model);
		$form->addField('autocomplete/Basic','member')->setModel('Model_StockNew_Member');

		$form->addSubmit('Filter');

		$form->add('Controller_StockNewFieldFilter',['branch_field'=>'branch','container_field'=>'container','container_row_field'=>'container_row']);

		$filter_array=[];

		$filter_array['for_branch_id'] = $this->app->stickyGET('branch');
		$filter_array['for_container_id'] = $this->app->stickyGET('container');
		$filter_array['for_container_row_id'] = $this->app->stickyGET('container_row');
		$filter_array['for_member_id'] = $this->app->stickyGET('member');

		$item_stock = $this->add('Model_StockNew_ItemStock',$filter_array);

		$avg_rate_on_date = $this->api->nextDate($this->app->now);

		$item_stock->addExpression('avg_factor_add_qty')->set(function($m,$q)use($avg_rate_on_date){
			$purchase_tra = $this->add('Model_StockNew_Transaction');
			$purchase_tra->addCondition('item_id',$m->getElement('id'));
			$purchase_tra->addCondition('created_at','<',$avg_rate_on_date);
			$purchase_tra->addCondition('transaction_template_type',array('Purchase','Opening'));

			return $q->expr('IFNULL([0],0)',[$purchase_tra->sum('qty')]);
		});

		$item_stock->addExpression('avg_factor_add_rate')->set(function($m,$q)use($avg_rate_on_date){
			$purchase_tra = $this->add('Model_StockNew_Transaction');
			$purchase_tra->addCondition('item_id',$m->getElement('id'));
			$purchase_tra->addCondition('created_at','<',$avg_rate_on_date);
			$purchase_tra->addCondition('transaction_template_type',array('Purchase','Opening'));

			return $q->expr('IFNULL([0],0)',[$purchase_tra->sum('rate')]);
		});

		$item_stock->addExpression('avg_factor_sub_qty')->set(function($m,$q)use($avg_rate_on_date){
			$return_tra = $this->add('Model_StockNew_Transaction');
			$return_tra->addCondition('item_id',$m->getElement('id'));
			$return_tra->addCondition('created_at','<',$this->api->nextDate($avg_rate_on_date));
			$return_tra->addCondition('transaction_template_type','Purchase_Return');

			return $q->expr('IFNULL([0],0)',[$return_tra->sum('qty')]);
		});

		$item_stock->addExpression('avg_factor_sub_rate')->set(function($m,$q)use($avg_rate_on_date){
			$return_tra = $this->add('Model_StockNew_Transaction');
			$return_tra->addCondition('item_id',$m->getElement('id'));
			$return_tra->addCondition('created_at','<',$this->api->nextDate($avg_rate_on_date));
			$return_tra->addCondition('transaction_template_type','Purchase_Return');

			return $q->expr('IFNULL([0],0)',[$return_tra->sum('rate')]);
		});
		
		$item_stock->addExpression('avg_rate')->set(function($m,$q){
			return $q->expr('IFNULL( (([avg_factor_add_rate] - [avg_factor_sub_rate])/([avg_factor_add_qty]-[avg_factor_sub_qty])),0)',[
					'avg_factor_add_rate'=>$m->getElement('avg_factor_add_rate'),
					'avg_factor_sub_rate'=>$m->getElement('avg_factor_sub_rate'),
					'avg_factor_add_qty'=>$m->getElement('avg_factor_add_qty'),
					'avg_factor_sub_qty'=>$m->getElement('avg_factor_sub_qty'),
				]);
		});

		$grid = $this->add('Grid');
		$grid->setModel($item_stock);
		$grid->removeColumn('name');
		$grid->removeColumn('code');
		$grid->removeColumn('allowed_in_transactions');
		$grid->removeColumn('description');
		$grid->removeColumn('avg_factor_add_rate');
		$grid->removeColumn('avg_factor_add_qty');
		$grid->removeColumn('avg_factor_sub_qty');
		$grid->removeColumn('avg_factor_sub_rate');

		if($form->isSubmitted()){
			$grid->js()->reload($form->get())->execute();
		}
	}

	function page_distribution(){
		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','item')->setModel('StockNew_Item');
		$form->addSubmit('Get Report');

		$item = $this->add('Model_StockNew_ItemStock',['for_branch_id'=>$this->app->current_branch->id]);
		$grid = $this->add('Grid');
		$grid->setModel($item);

		if($form->isSubmitted()){
			$grid->js()->reload(['item'=>$form['item']])->execute();
		}
	}
}