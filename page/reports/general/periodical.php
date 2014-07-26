<?php

class page_reports_general_periodical extends Page {
	public $title="Periodical Repots";
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('autocomplete/Basic','dealer');
		$dealer_field->setModel('Dealer');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		if($_GET['filter']){


		}

		$account=$this->add('Model_Account');
		$grid->setModel($account);
		$grid->addPaginator(50);


		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date'],'filter'=>1))->execute();
		}	

	}

	function page_accounts(){
		$this->api->stickyGET('accounts_id');

		$account=$this->add('Model_Account');

		$grid=$this->add('Grid');
		$grid->setModel($account);
		$grid->addPaginator(50);
	}
}
