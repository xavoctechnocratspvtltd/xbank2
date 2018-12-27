<?php

class page_stocknew_transactions extends Page {

	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DropDown','transaction_template')->setModel('StockNew_TransactionTemplate','name');
		$form->addSubmit('DO');

		if($tr=$this->app->stickyGET('transaction_template')){
			$tr_m = $this->add('Model_StockNew_TransactionTemplate')->load($tr);
			$f= $this->add("Form_StockTransaction",['transaction_template_model'=>$tr_m]);

			if($f->isSubmitted()){
				$f->process();
				$f->js(null,$f->js()->univ()->successMessage('Done'))->reload()->execute();
			}
		}

		if($form->isSubmitted()){
			$this->js()->reload($form->get())->execute();
		}
		
	}
}