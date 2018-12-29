<?php

class page_stocknew_reports extends Page {
	
	function page_index(){

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

		if($form->isSubmitted()){
			$grid->js()->reload($form->get())->execute();
		}

	}
}