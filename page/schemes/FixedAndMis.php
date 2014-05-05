<?php

class page_schemes_FixedAndMis extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Scheme_FixedAndMis',array('name','MinLimit','MaxLimit','Interest','AccountOpenningCommission','ReducingOrFlatRate','ActiveStatus','balance_sheet_id','InterestToAnotherAccount','MaturityPeriod','ProcessingFeesinPercent','ProcessingFees','SchemePoints','SchemeGroup'));

		if($crud->grid)
			$crud->grid->addPaginator(10);
		
	}
}