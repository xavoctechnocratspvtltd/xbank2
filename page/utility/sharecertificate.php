<?php

class page_utility_sharecertificate extends Page{
	public $title ='Share Certificate';
	function init(){
		parent::init();

		$account=$this->add('Model_Account_SM');
		$form=$this->add('Form');
		$acc_field=$form->addField('autocomplete/Basic','account')->validateNotNull();
		$acc_field->setModel($account);
		$form->addSubmit('Print');

		if($form->isSubmitted()){
			$this->js()->univ()->newWindow($this->api->url('utility_shareprint',
															array('account_id'=>$form['account'],
															'cut_page'=>1
															)
					)
					)->execute();	
		}
	}	
}