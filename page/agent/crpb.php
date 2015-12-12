<?php

class page_agent_crpb extends Page {
	public $title="Agent CRPB Reports";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		// $agent=$form->addField('autocomplete/Basic','agent');
		// $agent->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET List');

		$agent_model = $this->add('Model_Agent',array('from_date'=>$_GET['from_date'],'to_date'=> $_GET['to_date']));
		$agent_model->addCondition('id',$this->api->auth->model->id);
		$m_m = $agent_model->getElement('member_id')->getModel();
		$m_m->title_field="name";

		$member_join=$agent_model->join('members','member_id');
		// $member_join->addField('FatherName');
		// $member_join->addField('PermanentAddress');
		// $member_join->addField('PanNo');
		$member_join->addField('PhoneNos');
		// $member_join->addField('branch_id');

		if($this->api->stickyGET('filter')){
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			// if($agent_id = $this->api->stickyGET('agent')){
			// 	$agent_model->addCondition('id',$agent_id);
			// }


		}

		$grid_1= $this->add('Grid');
		$grid_1->setModel($agent_model,array('member','PhoneNos','account','self_crpb','total_group_crpb','code','cadre','total_group_count','self_business','total_team_business'));

		$grid_1->addPaginator(20);

		$this->add('VirtualPage')->addColumn('team_members',"Team Members",null,$grid_1)->set(function($p){
			$grid = $p->add('Grid');
			$agent_level_1 = $p->add('Model_Agent',array('from_date'=>$_GET['from_date'],'to_date'=> $_GET['to_date']));
			
			$m_m = $agent_level_1->getElement('member_id')->getModel();
			$m_m->title_field="name";
			
			$agent_level_1->addCondition('sponsor_id',$p->id);
			$grid->setModel($agent_level_1,array('member','PhoneNos','account','created_at','self_crpb','self_business'));
		});

		if($form->isSubmitted()){
			$grid_1->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
