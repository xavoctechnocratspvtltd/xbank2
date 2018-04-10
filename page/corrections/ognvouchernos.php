<?php


class page_corrections_ognvouchernos extends Page {
	function init(){
		parent::init();

		$f_year = $this->api->getFinancialYear($this->app->now);	
		$start_date = $f_year['start_date'];
		$end_date = $f_year['end_date'];
		
		$model = $this->add('Model_Transaction');
		$model->addCondition('branch_id',5);
		$model->addCondition('created_at','>=',$start_date);
		$model->setOrder('updated_at,id');

		$grid = $this->add('Grid');
		$grid->setModel($model->debug());
	}
}