<?php

class page_schemes_DDS extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Scheme_DDS',array('name','MinLimit','MaxLimit','Interest','AccountOpenningCommission','ActiveStatus','balance_sheet_id','MaturityPeriod','SchemePoints','SchemeGroup'));

		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}