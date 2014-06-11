<?php

class page_reports_deposit_fdProvision extends Page {
	public $title='FD Provision Report';

	function init(){
		parent::init();

		

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('Go');



	}
}