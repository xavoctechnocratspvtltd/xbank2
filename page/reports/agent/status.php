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

		$grid=$this->add("Grid_AccountsBase");
		$grid->add('H3',null,'grid_buttons')->set('Agent status As On '. date('d-M-Y',strtotime($till_date))); 
		$agent_model=$this->add('Model_Agent');

		$member_join=$agent_model->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PanNo');
		$member_join->addField('PhoneNos');
		$member_join->addField('member_branch_id','branch_id');

		$sb_acc_join = $agent_model->join('accounts','account_id');
		$sb_acc_join->addField('branch_id');

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


		$agent_model->addExpression('sponsor_phone')->set(function($m,$q){
			$sponsor = $m->add('Model_Agent',array('table_alias'=>'spns'));
			$sponsor_member_j = $sponsor->join('members','member_id');
			$sponsor_member_j->addField('PhoneNos');

			$sponsor->addCondition('id',$m->getElement('sponsor_id'));
			return $sponsor->fieldQuery('PhoneNos');
		});

		$agent_model->addExpression('sponsor_cadre')->set(function($m,$q){
			$sponsor = $m->add('Model_Agent',array('table_alias'=>'sponsor_cadre'));

			$sponsor->addCondition('id',$m->getElement('sponsor_id'));
			return $sponsor->fieldQuery('cadre');
		});

		$agent_model->addExpression('last_account_date')->set(function($m,$q){
			$acc = $m->add('Model_Account',array('table_alias'=>'last_account'));
			$acc->addCondition('agent_id',$m->getElement('id'));
			$acc->setOrder('created_at','desc');
			$acc->setLimit(1);
			return $acc->fieldQuery('created_at');
		});

		// $agent_model->addCondition('branch_id',$this->api->current_branch->id);

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('status');
			if($_GET['status']=='Inactive')
				$agent_model->addCondition('status',0);
			if($_GET['status']=='Active')
				$agent_model->addCondition('status','>',0);
		}else
			$agent_model->addCondition('id',-1);

		$agent_model->addCondition('agent_member_name','not like','%Default%');

		$agent_model->add('Controller_Acl');

		$grid->setModel($agent_model,array('code','agent_member_name','FatherName','PermanentAddress','PhoneNos','PanNo','account','cadre','current_individual_crpb','last_account_date','sponsor','sponsor_phone','sponsor_cadre'));
		$grid->addPaginator(50);

		$grid->addSno();
		$grid->addFormatter('agent_member_name','wrap');
		$grid->addFormatter('PermanentAddress','wrap');
		$grid->addFormatter('account','wrap');

		$grid->removeColumn('ActiveStatus');
		// $grid->controller->importField('status');

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){
			$grid->js()->reload(array('status'=>$form['status'],'filter'=>1))->execute();
		}
	}
}