<?php

class page_noclog_returnreceive extends Page {

	public $title = 'Return Receive NOC';

	function init(){
		parent::init();
		
		$this->add('Controller_Acl');
		
		$noc_model = $this->add('Model_NocLog');
		$noc_model->addExpression('account_number')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('AccountNumber')]);
		});
		$noc_model->addExpression('member_name')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('accounts_id')->fieldQuery('member_name_only')]);
		});
		
		// if($this->app->current_branch->id AND !$this->app->auth->model->isSuper())
		// 	$noc_model->addCondition('to_branch_id',$this->app->current_branch->id);

		$noc_model->addCondition('is_return',true);

		$noc_model->setOrder('send_at','desc');
		$grid = $this->add('Grid');
		$grid->addSno();
		$grid->setModel($noc_model,['account_number','member_name','noc_letter_received_on','send_at','created_by','received_by','received_at','received_narration','from_branch','to_branch','received_by','return_by','return_received','send_at','send_narration','received_narration','is_return','return_at','return_narration','return_received_narration','return_received_by','accounts_id','from_branch_id','to_branch_id','created_by_id','received_by_id','dispatch_by_id','return_by_id','return_received_by_id']);

		$grid->addHook('formatRow',function($g){
			$g->current_row_html['created_by'] = 'Created By: '.$g->model['created_by']."<br/> From Branch: ".$g->model['from_branch']."<br/>"."Narration: ".$g->model['send_narration'];
			$g->current_row_html['received_narration'] = 'Receive By: '.$g->model['received_by']."<br/>Branch: ".$g->model['to_branch']."<br/>"."Narration: ".$g->model['received_narration'];
			$g->current_row_html['return_narration'] = 'Return By: '.$g->model['return_by']."<br/>"."Narration: ".$g->model['return_narration'];
			$g->current_row_html['return_received_narration'] = 'Return Receive By: '.$g->model['return_received_by']."<br/>"."Narration: ".$g->model['return_received_narration'];
			
			if($g->model['received_at'] === "0000-00-00 00:00:00")
				$g->current_row_html['received_at'] = "-";
			else
				$g->current_row_html['received_at'] = $g->model['received_at'];
			if($g->model['dispatch_at'] === "0000-00-00 00:00:00")
				$g->current_row_html['dispatch_at'] = "-";
			else
				$g->current_row_html['dispatch_at'] = $g->model['dispatch_at'];

			if($g->model['return_at'] === "0000-00-00 00:00:00")
				$g->current_row_html['return_at'] = "-";
			else
				$g->current_row_html['return_at'] = $g->model['return_at'];
		});

		$remove_column = ['accounts_id','from_branch_id','to_branch_id','created_by_id','received_by_id','dispatch_by_id','return_by_id','return_received_by_id','from_branch','send_narration','received_by','to_branch','dispatch_by','return_by','return_received_by'];

		foreach ($remove_column as $key => $value) {
			$grid->removeColumn($value);
		}

		$grid->add('VirtualPage')
			->addColumn('action','Action')
			->set([$this,'action']);
	}

	function action($page){
		$id = $_GET[$page->short_name.'_id'];

		$view = $page->add('View');
		$noc_model = $view->add('Model_NocLog')->load($id);
		if($noc_model['return_received_by_id']){
			$view->add('View_Info')->setHtml('Return NOC Received By: '.$noc_model['return_received_by']."<br/> ".$noc_model['return_received_narration']);
			return;
		}

		$form = $view->add('Form',null,null,['form/stacked']);
		$form->addField('text','return_received_narration');
		$form->addSubmit('Return Received');
		if($form->isSubmitted()){
			$noc_model['return_received_narration'] = $form['return_received_narration'];
			$noc_model['return_received_by_id'] = $this->app->current_staff->id;
			$noc_model->save();
			$form->js(null,$view->js()->reload())->univ()->successMessage('Return NOC Received Successfully')->execute();
		}

	}

	function receive_received(){

	}

}