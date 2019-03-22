<?php

class page_noclog_return extends Page {
	public $title='NOC Return';

	function init(){
		parent::init();
		
		$this->add('Controller_Acl');
		
		$id = $this->app->stickyGET('recordid');

		$view = $this->add('View');
		$noc_model = $view->add('Model_NocLog')->load($id);

		if(!$noc_model['received_by_id']){
			$view->add('View_Info')->setHtml('First Receive the NOC, after then Return to Head Office');
			return;
		}
		if($noc_model['is_dispatch_to_customer']){
			$view->add('View_Error')->setHtml('NOC Dispatched To Customer, Cannot Return');
			return;
		}

		if($noc_model['is_return']){
			$view->add('View_Error')->setHtml('NOC is already return to head office');
			return;
		}
		
		$form = $view->add('Form',null,null,['form/stacked']);
		$form->addField('text','return_narration');
		$form->addSubmit('Return to Head Office');

		if($form->isSubmitted()){
			$noc_model['is_return'] = 1;
			$noc_model['return_at'] = $this->app->now;
			$noc_model['return_by_id'] = $this->app->current_staff->id;
			$noc_model['return_narration'] = $form['return_narration'];
			$noc_model->save();
			$form->js(null,$view->js()->reload())->univ()->successMessage('NOC Return to Head Office Successfully')->execute();
		}

	}
}