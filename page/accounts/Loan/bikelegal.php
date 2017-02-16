<?php

/**
* 
*/

class Model_Loan_BikeLegal extends Model_Active_Account_Loan {}

class page_accounts_Loan_bikelegal extends Page{
	
	function init(){
		parent::init();

		$this->add('Controller_Acl');
		$model= $this->add('Model_Loan_BikeLegal');
		$model->getElement('AccountNumber')->readOnly(true);

		$model->addHook('beforeSave',function($m){
			if(!$m['bike_surrendered'] && $m['bike_surrendered_on'])
				throw $m->exception('Please select this','ValidityCheck')->setField('bike_surrendered');
			if(!$m['is_in_legal'] && $m['legal_filing_date'])
				throw $m->exception('Please select this','ValidityCheck')->setField('is_in_legal');
		});

		$crud = $this->add('CRUD',['allow_add'=>false, 'allow_del'=>false]);
		$crud->setModel($model,['bike_surrendered','bike_surrendered_on','is_in_legal','legal_filing_date'],['AccountNumber','bike_surrendered','bike_surrendered_on','is_in_legal','legal_filing_date']);
		$crud->add('Controller_Acl');

		$crud->grid->addPaginator(50);
		$crud->grid->addQuickSearch(['AccountNumber']);
	}
}