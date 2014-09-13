<?php

class page_reports_agent_status extends Page {
	public $title="Active/ Inactive Report";
	function init(){
		parent::init();
		
		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$form->addField('dropdown','status')->setValueList(array('Inactive'=>'Inactive','Active'=>'Active'))->setEmptyText('Please Select')->validateNotNull();
		$form->addSubmit('GET LIST');

		$grid=$this->add("Grid");
		$grid->add('H3',null,'grid_buttons')->set('Agent status As On '. date('d-M-Y',strtotime($till_date))); 
		$agent_model=$this->add('Model_Agent');

		$member_join=$agent_model->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PanNo');
		$agent_model->addExpression('status')->set(function($m,$q){
			$date = $m->api->previousMonth(
						$m->api->previousMonth(
							$m->api->previousMonth($m->api->today)
							)
						);
			$acc=$m->add('Model_Account',array('table_alias'=>'xa'));
			$acc->addCondition('agent_id',$q->getField('id'));
			$acc->addCondition('created_at','>=',$date);
			return $acc->count();
		})->type('boolean');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('status');
			if($_GET['status']=='Inactive')
				$agent_model->addCondition('status',false);
			if($_GET['status']=='Active')
				$agent_model->addCondition('status',true);
		}else
			$agent_model->addCondition('id',-1);

		$grid->setModel($agent_model);
		$grid->addPaginator(50);
		$grid->removeColumn('sponsor');
		$grid->removeColumn('name');
		$grid->removeColumn('ActiveStatus');
		$grid->controller->importField('status');

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);

		if($form->isSubmitted()){
			$grid->js()->reload(array('status'=>$form['status'],'filter'=>1))->execute();
		}
	}
}