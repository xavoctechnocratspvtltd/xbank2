<?php

class page_reports_dealerdsa_dealer extends Page {
	
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','dealer')->setModel('Dealer');
		$form->addSubmit('Details');

		$grid = $this->add('Grid_AccountsBase');
		$m = $this->add('Model_Dealer');

		if($_GET['dealer_id']){
			$m->addCondition('id',$_GET['dealer_id']);
		}else{
			$m->addCondition('id',-1);
		}

		$grid->setModel($m);
		$grid->addSno();
		$grid->addPaginator(50);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer_id'=>$form['dealer']))->execute();
		}

	}
}