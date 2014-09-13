<?php

class page_reports_agent_detail extends Page {
	public $title="Agent Detail";
	function init(){
		parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$agent_field=$form->addField('dropdown','agent')->setEmptyText('Please Select');
		$agent_field->setModel('Agent');

		$form->addSubmit('GET LIST');
		$agent_guarantor=$this->add('Model_AgentGuarantor');
		$member_join=$agent_guarantor->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PhoneNos');

		$agent=$this->add('Model_Agent');
		if($_GET['agent']){
			$agent->addCondition('id',$_GET['agent']);
			$agent_guarantor->addCondition('agent_id',$_GET['agent']);
		}

		$view=$this->add('View');
		$grid_agent=$view->add('Grid');

		$grid_agent->add('H3',null,'grid_buttons')->set('Agent Detail As On '. date('d-M-Y',strtotime($till_date))); 
		
		$view->add('H3')->set('Agent Guarantor');

		$grid_agent_guarantor=$view->add('Grid');

		$grid_agent->setModel($agent);
		$grid_agent_guarantor->setModel($agent_guarantor);

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid_agent->js('click',$js);


		if($form->isSubmitted()){

			$view->js()->reload(array('agent'=>$form['agent']))->execute();

		}
	}
}