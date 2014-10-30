<?php

class page_stock_reports_store extends Page {
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$container_field=$form->addField('dropdown','container')->setEmptyText('All');
		$container_field->setModel('Stock_Container');

		$form->addSubmit('GET LIST');

		$grid=$this->add('Grid_AccountsBase');
		$row_model=$this->add('Model_Stock_Row');
		if($_GET['container'])
			$row_model->addCondition('container_id',$_GET['container']);
		$grid->addSno();
		$grid->setModel($row_model);
		$grid->addColumn('expander','detail');

		if($form->isSubmitted()){
			$grid->js()->reload(array('container'=>$form['container']))->execute();
		}
	}


	function page_detail(){
		$this->api->stickyGET('stock_rows_id');
		$items=$this->add('Model_Stock_Item');
		$items->addCondition('row_id',$_GET['stock_rows_id']);
		$items->tryLoadAny();
		$grid=$this->add('Grid_AccountsBase');
		$grid->setModel($items);
		$grid->removeColumn('container');
		$grid->removeColumn('row');
		$grid->removeColumn('is_consumable');
		$grid->removeColumn('is_issueable');
		$grid->removeColumn('is_fixedassets');
	}
}