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

		// $form->addField('DatePicker','from_date');
		// $form->addField('DatePicker','to_date');
		// $form->addField('dropdown','status')->setValueList(['all'=>'All','0'=>'InActive','1'=>'Active']);

		$document=$this->add('Model_Document');
		$document->addCondition('AgentDocuments',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}

		$form->addSubmit('GET LIST');

		$agent_guarantor=$this->add('Model_AgentGuarantor');
		$member_join=$agent_guarantor->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PhoneNos');
		// $member_join->addField('gurantor_branch_id','branch_id');

		// $agent_guarantor->addCondition('gurantor_branch_id',$this->api->current_branch->id);

		$agent=$this->add('Model_Agent');
		$member_join=$agent->leftJoin('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PanNo');
		$member_join->addField('PhoneNos');
		// $member_join->addField('branch_id');

		// $agent->add('Controller_Acl');
		// $agent->addCondition('branch_id',$this->api->current_branch->id);

		$agent->addExpression('is_default')->set(function($m,$q){
			$def_acc = $m->add('Model_Account',array('table_alias'=>'dfac_4_agent'));
			$def_acc->addCondition('DefaultAC',true);
			$def_acc->addCondition('member_id',$m->getElement('member_id'));
			return $def_acc->count();
		});

		// $agent->addCondition('is_default',0);

		$agent->addExpression('sponsor_phone')->set(function($m,$q){
			$sponsor = $m->add('Model_Agent',array('table_alias'=>'spns'));
			$sponsor_member_j = $sponsor->join('members','member_id');
			$sponsor_member_j->addField('PhoneNos');

			$sponsor->addCondition('id',$m->getElement('sponsor_id'));
			return $sponsor->fieldQuery('PhoneNos');
		});
		$agent->addExpression('sponsor_cadre')->set(function($m,$q){
			return $m->refSQL('sponsor_id')->fieldQuery('cadre');	
		});
		// $agent->addExpression('sponsor_cadre')->set(function($m,$q){
		// 	$sponsor = $m->add('Model_Agent',array('table_alias'=>'sponsor_cadre'));
		// 	$sponsor->addCondition('id',$m->getElement('sponsor_id'));
		// 	return $sponsor->fieldQuery('cadre');
		// });

		$agent->getElement('created_at')->caption('Date Of Joining');

		// $agent->addExpression('total_team_member')->set("'TODO'");
		// $agent->addExpression('team_business')->set("'TODO'");
		// $agent->addExpression('self_business')->set($this->add('Model_Account')->addCondition('agent_id',$agent->getElement('id'))->addCondition('SchemeType',array('DDS','FixedAndMis','Recurring'))->sum('Amount') );

		if($_GET['team_sponsor_id']){
			$agent->addCondition('sponsor_id',$_GET['team_sponsor_id']);
		}

		$grid_column_array = array('mo','code','agent_member_name',/*'level_1_crpb','level_2_crpb','level_3_crpb','total_group_crpb',*/'FatherName','PermanentAddress','PhoneNos','PanNo','account','cadre','created_at','sponsor','sponsor_cadre','sponsor_phone',/*'self_business','team_business','self_crpb','total_team_business','total_group_crpb',*/'added_by','ActiveStatus');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$agent->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}

			if($_GET['agent']){
				$agent->addCondition('id',$_GET['agent']);
				$agent_guarantor->addCondition('agent_id',$_GET['agent']);
			}

			// if($this->app->stickyGET('from_date')){
			// 	$agent->addCondition('created_at','>',$_GET['from_date']);
			// }

			// if($this->app->stickyGET('to_date')){
			// 	$agent->addCondition('created_at','<=',$this->app->nextDate($_GET['to_date']));
			// }

			// if($this->app->stickyGET('status') !=='all')
			// 	$agent->addCondition('ActiveStatus',$_GET['status']==0?false:true);

			$agent->tryLoadAny();
		}else{
			$agent->addCondition('id',-1);
			$agent_guarantor->addCondition('agent_id',-1);
		}

			


		$view=$this->add('View');
		$grid_agent=$view->add('Grid_AccountsBase');

		$grid_agent->add('H3',null,'grid_buttons')->set('Agent Detail As On '. date('d-M-Y'));
		$grid_agent->setModel($agent, $grid_column_array);
		$grid_agent->addSno();
		$grid_agent->addFormatter('sponsor','wrap');
		
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
		$grid_agent->addFormatter('PermanentAddress','wrap');
		$grid_agent->addFormatter('account','wrap');

		$grid_agent->addPaginator(500);


		$this->add('VirtualPage')->addColumn('team_members',"Team Members",null,$grid_agent)->set(function($p){
			$grid = $p->add('Grid');
			$agent_level_1 = $p->add('Model_Agent');
			
			$m_m = $agent_level_1->getElement('member_id')->getModel();
			$m_m->title_field="name";
			
			$agent_level_1->addCondition('sponsor_id',$p->id);
			$grid->setModel($agent_level_1,array('member','PhoneNos','account','created_at','self_crpb','self_business'));
		});

		if($form->isSubmitted()){
			$send =['agent'=>$form['agent'],/*'from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'status'=>$form['status']?:null,*/'filter'=>1];
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}

			$view->js()->reload($send)->execute();

		}
	}
}