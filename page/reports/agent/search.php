<?php

class page_reports_agent_search extends Page {
	public $title="Agent Search";
	function init(){
		parent::init();

		$till_date=$till_date=$this->api->today;
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}
		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','branch')->setModel('Model_Branch');
		$form->addSubmit('GET List');


		$grid=$this->add('Grid_AccountsBase'); 
		$grid->add('H3',null,'grid_buttons')->set('Agent List As On '. date('d-M-Y',strtotime($till_date))); 
		$agent_model=$this->add('Model_Agent');
		
		$agent_model->addExpression('branch_id')->set(function($m,$q){
			return $m->refSQL('account_id')->fieldQuery('branch_id');
		});

		if($_GET['filter']){
			$this->api->stickyGET('filter');

			if($_GET['from_date']){
				$this->api->stickyGET('from_date');
				$agent_model->addCondition('created_at','>=',$_GET['from_date']);
			}
			if($_GET['to_date']){
				$this->api->stickyGET('to_date');
				$agent_model->addCondition('created_at','<=',$_GET['to_date']);
			}
			if($_GET['branch']){
				$this->api->stickyGET('branch');
				$agent_model->addCondition('branch_id',$_GET['branch']);
			}

		}else{
			$agent_model->addCondition('id',-1);
		}
		
		$grid->add('Controller_Acl');
		$grid->setModel($agent_model,array('code','agent_member_name','account','agent_member_father_name','agent_member_address','agent_phone_no','agent_pan_no','added_by','ActiveStatus','created_at'));

		$paginator = $grid->addPaginator(500);
		$grid->skip_var = $paginator->skip_var;

		$grid->addSno();



		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);
	


		if($form->isSubmitted()){
			$send = array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'branch'=>$form['branch'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	
	}
}		