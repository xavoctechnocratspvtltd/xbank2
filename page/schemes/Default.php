<?php

class page_schemes_Default extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Scheme_Default',array('name','MinLimit','MaxLimit','Interest','AccountOpenningCommission','ReducingOrFlatRate','ActiveStatus','balance_sheet_id','ProcessingFees','SchemePoints','SchemeGroup','isDepriciable','DepriciationPercentBeforeSep','DepriciationPercentAfterSep'));

		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}