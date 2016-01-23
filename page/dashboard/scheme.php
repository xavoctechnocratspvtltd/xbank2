<?php

class page_dashboard_scheme extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Scheme ','icon'=>'flag'));

		$grid=$this->add('Grid_AccountsBase');
		
		$scheme_model=$this->add('Model_Scheme',array('table_alias'=>'xa'));

		$grid->setModel($scheme_model);
		$grid->addSno();
		$grid->addFormatter('name','wrap');
		$grid->addFormatter('AccountOpenningCommission','100Wrap');
		$grid->addFormatter('CollectorCommissionRate','100Wrap');

	}
}