<?php

class page_utility_intrestcertificate extends Page{
	public $title ='INTREST CERTIFICATE ';
	function init(){
		parent::init();

		$form=$this->add('Form');
		$account_field = $form->addField('autocomplete/Basic','member')->validateNotNull();
		$account_field->setModel('Member');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('Get It');

		if($form->isSubmitted()){
			$this->js()->univ()->newWindow($this->api->url('utility_intrestprint',
															array(
																'member_id'=>$form['member'],
																'from_date'=>$form['from_date'],
																'to_date'=>$form['to_date'],
																'cut_page'=>1
															)
					)
					)->execute();
		}

	}	
}