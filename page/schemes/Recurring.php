<?php

class page_schemes_Recurring extends Page{
	function init(){
		parent::init();

		$crud=$this->add('CRUD');
		$crud->setModel('Scheme_Recurring',array('name','MinLimit','MaxLimit','Interest','PremiumMode','AccountOpenningCommission','NumberOfPremiums','ActiveStatus','balance_sheet_id','MaturityPeriod','SchemePoints','SchemeGroup','CollectorCommissionRate'));

		if($crud->grid)
			$crud->grid->addPaginator(10);
	}
}