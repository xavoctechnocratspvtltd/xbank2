<?php

class page_agent_activeinactiveaccount extends Page {
	public $title="Agent Active InActive Account ";
	function init(){
		parent::init();

		$till_date=$till_date=$this->api->today;
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}
		$form=$this->add('Form');

		$form->addField('DropDown','account_type')->setValueList(array('%'=>'All','DDS'=>'DDS','Recurring'=>'Recurring','FD'=>'FD','MIS'=>'MIS'));
		$form->addField('DropDown','status')->setValueList(array('All'=>'All',"Active"=>'Active',"InActive"=>'InActive'));
		$form->addSubmit('GET List');

		$view = $this->add('View');

		$rd_grid=$view->add('Grid_AccountsBase'); 
		$rd_grid->add('H3',null,'grid_buttons')->set('RD Account Active / InActive Account List As On '. date('d-M-Y',strtotime($till_date))); 
		$rd_account_model=$this->add('Model_Account_Recurring');
		$rd_account_model->addExpression('FatherName')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('FatherName');
		});

		$rd_account_model->addExpression('address')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('CurrentAddress');
		});
		$rd_account_model->addExpression('phone_no')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('PhoneNos');
		});

		$rd_account_model->addCondition('agent_id',$this->api->auth->model->id);

		if($_GET['filter']){
			$this->api->stickyGET('filter');

			if($_GET['account_type']){
				$this->api->stickyGET('account_type');
				$rd_account_model->addCondition('account_type','like',$_GET['account_type']);
			}
			if($_GET['status']){
				$this->api->stickyGET('status');
				if($_GET['status']=='InActive'){
					$rd_account_model->addCondition([['ActiveStatus',false],['ActiveStatus',null]]);
				}
				if($_GET['status']=='Active'){
					$rd_account_model->addCondition('ActiveStatus',true);
				}
			}	
		}else{
			$rd_account_model->addCondition('id',-1);
		}
		
		$rd_grid->setModel($rd_account_model,array('sno','AccountNumber','created_at','maturity_date','name','FatherName','address','phone_no','Amount','ActiveStatus'));

		$paginator = $rd_grid->addPaginator(500);
		$rd_grid->skip_var = $paginator->skip_var;

		$rd_grid->addSno();
		$rd_grid->addTotals(['Amount']);

		/*Fixed And Mis Grid*/

		$fixed_grid=$view->add('Grid_AccountsBase'); 
		$fixed_grid->add('H3',null,'grid_buttons')->set(' Fixed / MIS Account Active / InActive Account List As On '. date('d-M-Y',strtotime($till_date))); 
		$fixed_account_model=$this->add('Model_Account_FixedAndMis');
		$fixed_account_model->addExpression('FatherName')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('FatherName');
		});

		$fixed_account_model->addExpression('address')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('CurrentAddress');
		});
		$fixed_account_model->addExpression('phone_no')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('PhoneNos');
		});

		$fixed_account_model->addCondition('agent_id',$this->api->auth->model->id);
		if($_GET['filter']){
			$this->api->stickyGET('filter');

			if($_GET['account_type']){
				$this->api->stickyGET('account_type');
				$fixed_account_model->addCondition('account_type','like',$_GET['account_type']);
			}
			if($_GET['status']){
				$this->api->stickyGET('status');
				if($_GET['status']=='InActive'){
					$fixed_account_model->addCondition([['ActiveStatus',false],['ActiveStatus',null]]);
				}
				if($_GET['status']=='Active'){
					$fixed_account_model->addCondition('ActiveStatus',true);
				}
			}	
		}else{
			$fixed_account_model->addCondition('id',-1);
		}
		
		$fixed_grid->setModel($fixed_account_model,array('sno','AccountNumber','created_at','maturity_date','name','FatherName','address','phone_no','Amount','ActiveStatus'));

		$paginator = $fixed_grid->addPaginator(500);
		$fixed_grid->skip_var = $paginator->skip_var;

		$fixed_grid->addSno();
		$fixed_grid->addTotals(['Amount']);


		/*Fixed And Mis Grid*/

		$dds_grid=$view->add('Grid_AccountsBase'); 
		$dds_grid->add('H3',null,'grid_buttons')->set('DDS Account Active / InActive Account List As On '. date('d-M-Y',strtotime($till_date))); 
		$dds_account_model=$this->add('Model_Account_DDS');
		$dds_account_model->addExpression('FatherName')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('FatherName');
		});

		$dds_account_model->addExpression('address')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('CurrentAddress');
		});
		$dds_account_model->addExpression('phone_no')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('PhoneNos');
		});

		$dds_account_model->addCondition('agent_id',$this->api->auth->model->id);
		if($_GET['filter']){
			$this->api->stickyGET('filter');

			if($_GET['account_type']){
				$this->api->stickyGET('account_type');
				$dds_account_model->addCondition('account_type','like',$_GET['account_type']);
			}
			if($_GET['status']){
				$this->api->stickyGET('status');
				if($_GET['status']=='InActive'){
					$dds_account_model->addCondition([['ActiveStatus',false],['ActiveStatus',null]]);
				}
				if($_GET['status']=='Active'){
					$dds_account_model->addCondition('ActiveStatus',true);
				}
			}	
		}else{
			$dds_account_model->addCondition('id',-1);
		}
		
		$dds_grid->setModel($dds_account_model,array('sno','AccountNumber','created_at','maturity_date','name','FatherName','address','phone_no','Amount','ActiveStatus'));

		$paginator = $dds_grid->addPaginator(500);
		$dds_grid->skip_var = $paginator->skip_var;

		$dds_grid->addSno();
		$dds_grid->addTotals(['Amount']);

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);
	


		if($form->isSubmitted()){
			$send = array('account_type'=>$form['account_type'],'status'=>$form['status'],'filter'=>1);
			$view->js()->reload($send)->execute();

		}	
	}
}		