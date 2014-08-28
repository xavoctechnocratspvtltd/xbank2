<?php

class page_reports_agent_detail extends Page {
	public $title="Agent Detail";
	function init(){
		parent::init();

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
		
		$view->add('H3')->set('Agent Guarantor');

		$grid_agent_guarantor=$view->add('Grid');

		$grid_agent->setModel($agent);
		$grid_agent_guarantor->setModel($agent_guarantor);

		if($form->isSubmitted()){

			$view->js()->reload(array('agent'=>$form['agent']))->execute();

		}
	}
}