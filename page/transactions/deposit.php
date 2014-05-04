<?php

class page_transactions_deposit extends Page {
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','account')->setModel('Account');

		if($form->isSubmitted())
			$form->js()->univ()->successMessage($form['account'])->execute();

	}
}