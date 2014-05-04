<?php

class page_schemes extends Page {
	public $title= 'Scheme Management';
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		foreach (explode(',',ACCOUNT_TYPES) as $accounts) {
			$acc_tab = $tabs->addTab($accounts);
			$crud=$acc_tab->add('CRUD');
			$crud->setModel('Scheme_'.$accounts);
		}
	}
}