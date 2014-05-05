<?php

class page_schemes_CC extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Scheme_CC',array('name','MinLimit','MaxLimit','Interest','AccountOpenningCommission','ActiveStatus','balance_sheet_id','ProcessingFeesinPercent','ProcessingFees','SchemePoints','SchemeGroup'));

		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}