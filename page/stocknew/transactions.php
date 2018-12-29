<?php

class page_stocknew_transactions extends Page {

	function init(){
		parent::init();

		$form = $this->add('Form');
		$trt_field = $form->addField('DropDown','transaction_template')->setEmptyText('Select Transaction to execute')->validateNotNull();
		$trt_field->setModel('StockNew_TransactionTemplate','name');
		$form->addSubmit('DO');

		$trt_field->js('change',$form->js()->submit());

		if($tr=$this->app->stickyGET('transaction_template')){
			$tr_m = $this->add('Model_StockNew_TransactionTemplate')->load($tr);
			$f= $this->add("Form_StockTransaction",['transaction_template_model'=>$tr_m]);

			if($f->isSubmitted()){
				$f->process();
				$f->js(null,$f->js()->univ()->successMessage('Done'))->reload()->execute();
			}
			$trt_field->set($tr);
		}

		if($form->isSubmitted()){
			$this->js()->reload($form->get())->execute();
		}
		
	}
}