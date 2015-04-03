<?php

class page_reports_agent_detail extends Page {
	public $title="Agent Detail";
	function init(){
		parent::init();

		
		if($_GET['member_list']){
			$this->js()->univ()->frameURL('MemberList',$this->api->url(null,array('team_sponsor_id'=>$_GET['member_list'])))->execute();
		}

		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');

		$form->addSubmit('GET LIST');
		$agent_guarantor=$this->add('Model_AgentGuarantor');
		$member_join=$agent_guarantor->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PhoneNos');
		$member_join->addField('branch_id');

		$agent_guarantor->addCondition('branch_id',$this->api->current_branch->id);

		$agent=$this->add('Model_Agent');
		$member_join=$agent->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PanNo');
		$member_join->addField('PhoneNos');
		$member_join->addField('branch_id');

		$agent->addCondition('branch_id',$this->api->current_branch->id);

		$agent->addExpression('is_default')->set(function($m,$q){
			$def_acc = $this->add('Model_Account',array('table_alias'=>'dfac_4_agent'));
			$def_acc->addCondition('DefaultAC',true);
			$def_acc->addCondition('member_id',$m->getElement('member_id'));
			return $def_acc->count();
		});

		$agent->addCondition('is_default',false);

		$agent->addExpression('sponsor_phone')->set(function($m,$q){
			$sponsor = $m->add('Model_Agent',array('table_alias'=>'spns'));
			$sponsor_member_j = $sponsor->join('members','member_id');
			$sponsor_member_j->addField('PhoneNos');

			$sponsor->addCondition('id',$m->getElement('sponsor_id'));
			return $sponsor->fieldQuery('PhoneNos');
		});

		$agent->getElement('created_at')->caption('Date Of Joining');

		$agent->addExpression('current_cadre')->set("'TODO'");
		$agent->addExpression('total_team_member')->set("'TODO'");

		if($_GET['team_sponsor_id']){
			$agent->addCondition('sponsor_id',$_GET['team_sponsor_id']);
		}

		if($_GET['agent']){
			$agent->addCondition('id',$_GET['agent']);
			$agent_guarantor->addCondition('agent_id',$_GET['agent']);
		}



		$view=$this->add('View');
		$grid_agent=$view->add('Grid_AccountsBase');

		$grid_agent->add('H3',null,'grid_buttons')->set('Agent Detail As On '. date('d-M-Y')); 
		$grid_agent->setModel($agent,array('name','FatherName','PermanentAddress','PhoneNos','PanNo','account','sponsor','sponsor_phone','created_at','AgentCode','current_cadre','total_team_member'));
		$grid_agent->addSno();

		$grid_agent->addColumn('Button','member_list');
		
		$view->add('H3')->set('Agent Guarantor');
		$grid_agent_guarantor=$view->add('Grid');
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