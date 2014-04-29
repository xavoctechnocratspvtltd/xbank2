<?php

class page_schemes extends Page {
	public $title= 'Scheme Management';
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		foreach (explode(',',ACCOUNT_TYPES) as $accounts) {
			$acc_tab = $tabs->addTab($accounts);
			$form=$acc_tab->add('Form')->addClass('stacked');
			$model = $form->setModel('Scheme_'.$accounts);
			$model->manageForm($form);
		}


	}
}