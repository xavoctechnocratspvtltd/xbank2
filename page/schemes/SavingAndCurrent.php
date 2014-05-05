<?php

class page_schemes_SavingAndCurrent extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Scheme_SavingAndCurrent',array('name','MinLimit','MaxLimit','Interest','ActiveStatus','balance_sheet_id','SchemePoints','SchemeGroup','isDepriciable','DepriciationPercentBeforeSep','DepriciationPercentAfterSep'));

		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}