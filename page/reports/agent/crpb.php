<?php

class page_reports_agent_crpb extends Page {
	public $title="Agent CRPB Reports";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$agent=$form->addField('autocomplete/Basic','agent');
		$agent->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET List');

		$agent_model = $this->add('Model_Agent');

		$m_m = $agent_model->getElement('member_id')->getModel();
		$m_m->title_field="name";

		$member_join=$agent_model->join('members','member_id');
		// $member_join->addField('FatherName');
		// $member_join->addField('PermanentAddress');
		// $member_join->addField('PanNo');
		$member_join->addField('PhoneNos');
		// $member_join->addField('branch_id');

		$agent_model->addExpression('self_crpb')->set(function($m,$q){
			$acc = $m->add('Model_Account',array('self_crpb_account'));
			$acc->addCondition('agent_id',$q->getField('id'));

			if($_GET['from_date'])
				$acc->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$acc->addCondition('created_at','<',$m->api->nextDate($_GET['to_date']));

			return $acc->sum('crpb');

		});

		if($this->api->stickyGET('filter')){
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			if($agent_id = $this->api->stickyGET('agent')){
				$agent_model->addCondition('id',$agent_id);
			}


		}


		$grid=$this->add('Grid');
		
		$grid_1= $this->add('Grid');
		$grid_1->setModel($agent_model,array('member','PhoneNos','account','self_crpb'));

		$grid_1->addPaginator(20);


		$grid->add('H3',null,'grid_buttons')->set('For Close '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 
 							'member_id_name' => 'Smith', 
 							'phone_no'=>'2000', 
 							'saving_account_no'=>'1000', 
 							'self_crpb'=>'12-09-100',
 							'team_crpb'=>'any',
 							'agent_code'=>'any',
 							'current_cadre'=>'1000', 
 							'total_team_member'=>'1000', 
 							'self_total_business'=>'1000', 
 							'team_total_business'=>'1000', 
 							'team_member_list'=>'1000', 
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('member_id_name');
		$grid->addColumn('phone_no');
		$grid->addColumn('saving_account_no');
		$grid->addColumn('self_crpb');
		$grid->addColumn('team_crpb');
		$grid->addColumn('agent_code');
		$grid->addColumn('current_cadre');
		$grid->addColumn('total_team_member');
		$grid->addColumn('self_total_business');
		$grid->addColumn('team_total_business');
		$grid->addColumn('team_member_list');

		$grid->setSource($data);



		if($form->isSubmitted()){
			$grid_1->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
