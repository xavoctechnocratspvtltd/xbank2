<?php

class page_reports_agent_toptenmember extends Page {
	public $title="Top 10 Members";
	
	function init(){
		parent::init();
		
		$from_date = $this->app->stickyGET('from_date')?:"1970-01-01";
		$to_date = $this->app->stickyGET('to_date')?:$this->app->today;
		$agentid = $this->app->stickyGET('agent_id');

		$vp = $this->add('VirtualPage');
		$vp->set([$this,'displayAgentAccoutns']);

		$form=$this->add('Form');
		$field_agent = $form->addField('autocomplete/Basic','agent');
		$field_agent->setModel('Model_Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET LIST');

		$model = $this->add('Model_Agent');

		$model->addExpression('sub_agents_count')->set(function($m,$q)use($from_date,$to_date){
			return $m->add('Model_Agent',['table_alias'=>'sub_agents'])
				->addCondition('sponsor_id',$m->getElement('id'))
				->addCondition('created_at','>=',$from_date)
				->addCondition('created_at','<',$this->app->nextDate($to_date))
				->count()
				;
		})->type('int');
		$model->addCondition('sub_agents_count','>',0);
		if($agentid)
			$model->addCondition('id',$agentid);

		$model->setOrder('sub_agents_count','desc');

		$grid = $this->add('Grid_AccountsBase');
		$grid->setModel($model,['name','ActiveStatus','sub_agents_count','self_crpb','total_group_crpb']);
		$grid->addFormatter('name','Wrap');
		$grid->addPaginator(100);
		$grid->addSno();

		$grid->setFormatter('sub_agents_count','template')
				->setTemplate('<a class="sub_agents_count" href="#" data-agentid="{$id}">{$sub_agents_count}</a>','sub_agents_count');
		$grid->js('click')->_selector('.sub_agents_count')->univ()->frameURL([
											$vp->getUrl(),
											'agent_id'=>$this->js()->_selectorThis()->data('agentid'),
											// 'agent_from_date'=>$this->js()->_selectorThis()->data('agentfromdate'),
											// 'agent_to_date'=>$this->js()->_selectorThis()->data('agenttodate'),
										]);

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
					'agent_id'=>$form['agent']?:0,
				))->execute();
		}
	}

	function displayAgentAccoutns($page){
		$agent_id = $_GET['agent_id'];
		$from_date = $this->app->stickyGET('from_date')?:"1970-01-01";
		$to_date = $this->app->stickyGET('to_date')?:$this->app->today;

		$model = $page->add('Model_Agent')
			->addCondition('sponsor_id',$agent_id)
			->addCondition('created_at','>=',$from_date)
			->addCondition('created_at','<',$this->app->nextDate($to_date))
			;
		$model->setOrder('created_at','desc');
		$grid = $page->add('Grid_AccountsBase');
		$grid->setModel($model,['name','created_at','ActiveStatus','sub_agents_count','self_crpb','total_group_crpb']);
		$grid->addFormatter('name','Wrap');
		$grid->addSno();
	}
}