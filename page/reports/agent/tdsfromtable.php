<?php


class page_reports_agent_tdsfromtable extends Page {
	public $title='Agent TDS Report New';


	function init(){
		parent::init();

		$till_date = $this->api->today;
		$from_date = '01-01-1970';
		if($this->app->stickyGET('to_date')) $till_date = $_GET['to_date'];
		if($this->app->stickyGET('from_date')) $from_date = $_GET['from_date'];

		$form = $this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('DropDown','account_type')->setValueList(array('%'=>'All','DDS'=>'DDS','Recurring'=>'Recurring','FD'=>'FD','MIS'=>'MIS'));

		$form->addSubmit('Go');

		$model = $this->add('Model_AgentTDS');
		$model->addExpression('account_type')->set($model->refSQL('related_account_id')->fieldQuery('account_type'));
		$model->addExpression('PanNo')->set($model->refSQL('agent_id')->fieldQuery('agent_pan_no'));
		$model->getElement('branch')->sortable(true);
		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("from_date");
			$this->api->stickyGET("to_date");
			$this->api->stickyGET("account_type");
			$this->api->stickyGET("agent");


			if($_GET['account_type']){
				$model->addCondition('account_type','like',$_GET['account_type']);
			}

			if($_GET['from_date']){
				$model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}
			if($_GET['agent']){
				$model->addCondition('agent_id',$_GET['agent']);
			}
		}
		// else
		// 	$model->addCondition('id',-1);

		$grid = $this->add('Grid');
		$grid->setModel($model);

		$grid->addPaginator(200);

		$grid->removeColumn('transaction');

		$model->setOrder('created_at','desc');

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'filter'=>1,
					'from_date'=>$form['from_date']?:0,
					'agent'=>$form['agent']?:0,
					'to_date'=>$form['to_date']?:0,
					'account_type'=>$form['account_type']
				))->execute();
		}
	}

}