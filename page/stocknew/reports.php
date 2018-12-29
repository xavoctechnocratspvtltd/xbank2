<?php

class page_stocknew_reports extends Page {
	
	function page_index(){

		$tabs = $this->add('Tabs');
		$overall_stock_report_tab = $tabs->addTabURL($this->app->url('./overall'),'Over All Stock Report');
		$stock_distribution_report_tab = $tabs->addTabURL($this->app->url('./distribution'),'Stock Distribution Report');
		

	}

	function page_overall(){
		$this->add('H3')->set('Stock Report');

		$form = $this->add('Form');
		$form->addField('DropDown','branch')->setEmptyText('All')->setModel('Branch');
		$form->addField('DropDown','container')->setEmptyText('All')->setModel('StockNew_Container');
		$form->addField('DropDown','container_row')->setEmptyText('All')->setModel('StockNew_ContainerRow');
		$form->addField('autocomplete/Basic','member')->setModel('Model_StockNew_Member');

		$form->addSubmit('Filter');

		$form->add('Controller_StockNewFieldFilter',['branch_field'=>'branch','container_field'=>'container','container_row_field'=>'container_row']);

		$filter_array=[];

		$filter_array['for_branch_id'] = $this->app->stickyGET('branch');
		$filter_array['for_container_id'] = $this->app->stickyGET('container');
		$filter_array['for_container_row_id'] = $this->app->stickyGET('container_row');
		$filter_array['for_member_id'] = $this->app->stickyGET('member');

		$item_stock = $this->add('Model_StockNew_ItemStock',$filter_array);

		$grid = $this->add('Grid');
		$grid->setModel($item_stock);

		$grid->removeColumn('name');
		$grid->removeColumn('code');
		$grid->removeColumn('allowed_in_transactions');
		$grid->removeColumn('description');

		if($form->isSubmitted()){
			$grid->js()->reload($form->get())->execute();
		}
	}

	function page_distribution(){
		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','item')->setModel('StockNew_Item');
		$form->addSubmit('Get Report');

		$item = $this->add('Model_StockNew_ItemStock');
		$grid=$this->add('Grid');
		$grid->setModel($item);

		if($form->isSubmitted()){
			$grid->js()->reload(['item'=>$form['item']])->execute();
		}
	}
}