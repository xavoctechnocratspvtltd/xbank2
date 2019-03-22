<?php

class page_noclog_dispatch extends Page {
	public $title='NOC Dispatch to customer';

	function init(){
		parent::init();
		
		$this->add('Controller_Acl');
		
		$id = $this->app->stickyGET('recordid');

		$view = $this->add('View');
		$noc_model = $view->add('Model_NocLog')->load($id);
		if(!$noc_model['received_by_id']){
			$view->add('View_Info')->setHtml('First Receive the NOC, after then Dispatch');
			return;
		}
		if($noc_model['is_dispatch_to_customer']){
			$view->add('View_Info')->setHtml('NOC Dispatched To Customer <br/> Dispatch On: '.$noc_model['dispatch_at']."<br/> Dispatch Narration: ".$noc_model['dispatch_narration']." <br/> Dispatch By: ".$noc_model['dispatch_by']);
			return;
		}
		if($noc_model['is_return']){
			$view->add('View_Info')->setHtml('NOC is return to head office, cannot dispatch');
			return;
		}


		$form = $view->add('Form',null,null,['form/stacked']);
		$form->addField('checkbox','is_dispatch_to_customer')->set(1);
		$form->addField('text','dispatch_narration');
		$form->addSubmit('Dispatch');
		if($form->isSubmitted()){
			if(!$form['is_dispatch_to_customer']) $form->displayError('is_dispatch_to_customer','must not be empty');

			$noc_model['dispatch_narration'] = $form['dispatch_narration'];
			$noc_model['is_dispatch_to_customer'] = $form['is_dispatch_to_customer'];
			$noc_model['dispatch_at'] = $this->app->now;
			$noc_model['dispatch_by_id'] = $this->app->current_staff->id;
			$noc_model->save();
			$form->js(null,$view->js()->reload())->univ()->successMessage('NOC Dispatched Successfully')->execute();
		}


	}
}