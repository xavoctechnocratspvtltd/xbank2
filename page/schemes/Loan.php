<?php

class page_schemes_Loan extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Scheme_Loan',array('name','MinLimit','MaxLimit','Interest','ReducingOrFlatRate','PremiumMode','NumberOfPremiums','ActiveStatus','balance_sheet_id','ProcessingFeesinPercent','ProcessingFees','SchemePoints','SchemeGroup'));

		if($crud->grid)
			$crud->grid->addPaginator(10);
		
	}
}