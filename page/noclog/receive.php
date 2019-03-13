<?php

class page_noclog_receive extends Page {

	public $title = 'Receive NOC';

	function init(){
		parent::init();
		
		$noc_model = $this->add('Model_NocLog');
		if($this->app->current_branch->id)
			$noc_model->addCondition('to_branch_id',$this->app->current_branch->id);

		$grid = $this->add('Grid');
		$grid->setModel($noc_model,['accounts','noc_letter_received_on','to_branch','created_by','send_at','send_narration','received_at','received_narration','received_by_id']);

		$grid->add('VirtualPage')
			->addColumn('action','Action')
			->set([$this,'action']);
	}

	function action($page){
		$id = $_GET[$page->short_name.'_id'];

		$tabs = $page->add('Tabs');
		$received_tab = $tabs->addTab('Received');
		$tabs->addTabURL($this->app->url('noclog_dispatch',['recordid'=>$id]) ,'Dispatch');
		$tabs->addTabURL($this->app->url('noclog_return',['recordid'=>$id]),'Return');

		$view = $received_tab->add('View');
		$noc_model = $view->add('Model_NocLog')->load($id);
		if($noc_model['received_by_id']){
			$view->add('View_Info')->setHtml('NOC Received By: '.$noc_model['received_by']." ON Date: ".$noc_model['received_at']." <br/> ".$noc_model['received_narration']);
			return;
		}

		$form = $view->add('Form',null,null,['form/stacked']);
		$form->addField('text','received_narration');
		$form->addSubmit('Received');
		if($form->isSubmitted()){
			$noc_model['received_narration'] = $form['received_narration'];
			$noc_model['received_at'] = $this->app->now;
			$noc_model['received_by_id'] = $this->app->current_staff->id;
			$noc_model->save();
			$form->js(null,$view->js()->reload())->univ()->successMessage('NOC Received Successfully')->execute();
		}

	}

	function receive_received(){

	}

}