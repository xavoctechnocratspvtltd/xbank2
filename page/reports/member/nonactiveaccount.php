<?php
class page_reports_member_nonactiveaccount extends Page {
	public $title="Member Without Non Active SM Account";
	
	function init(){
		parent::init();

		$this->member = $this->add('Model_Member');
		$this->member->addExpression('sm_count')->set(function($m,$q){
			return  $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])->addCondition('member_id',$q->getField('id'))->count();
		});
		$this->grid = $this->add('Grid');
		$this->grid->addSno();
		$this->grid->addPaginator(500);


		$this->member->addExpression('saving_and_current_accounts')->set(function($m,$q){
			return $this->add('Model_Account_SavingAndCurrent',['table_alias'=>'saving_accounts'])
				->addCondition('member_id',$q->getField('id'))
				->addCondition('ActiveStatus',true)
				->_dsql()->del('fields')
				->field('GROUP_CONCAT(AccountNumber)');
		});


		$this->member->addExpression('sb_disactive')->set(function($m,$q){
			
				return  $this->add('Model_Account_SavingAndCurrent',['table_alias'=>'saving_accounts'])->addCondition('ActiveStatus',false)->addCondition('member_id',$q->getField('id'))->count();
		});


		// $this->member->addExpression('loan_account')->set(function($m,$q){
		// 	return $this->add('Model_Account_Loan')
		// 				->addCondition('member_id',$q->getField('id'))
		// 				->addCondition('ActiveStatus',false)
		// 				->_dsql()->del('fields')
		// 				->field('GROUP_CONCAT(AccountNumber)');
		// });


		// $this->member->addExpression('rd_account')->set(function($m,$q){
		// 	return $m->add('Model_Account_Recurring')
		// 				->addCondition('member_id',$q->getField('id'))
		// 				->addCondition('ActiveStatus',false)
		// 				->_dsql()->del('fields')
		// 				->field('GROUP_CONCAT(AccountNumber)');
		// });

		$this->member->addExpression('sm_accounts')->set(function($m,$q){
			return $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])
				->addCondition('member_id',$q->getField('id'))
				->addCondition('ActiveStatus',true)
				->_dsql()->del('fields')
				->field('GROUP_CONCAT(AccountNumber)');
		});

		$this->member->addExpression('non_active_accounts')->set(function($m,$q){
			

				return $m->add('Model_Account')
					->addCondition('member_id',$q->getField('id'))
					->addCondition('account_type',['FD',ACCOUNT_TYPE_DDS,ACCOUNT_TYPE_RECURRING,LOAN_AGAINST_DEPOSIT])
					->addCondition('ActiveStatus',false)
					->_dsql()->del('fields')
					->field('GROUP_CONCAT(AccountNumber)');
		});

		



		$this->member->addCondition('sm_count','>','0');
		$this->member->addCondition('sm_accounts','!=','');
		//$this->member->addCondition('non_active_accounts','!=','');
		$this->member->addCondition('sb_disactive','<','1');
		$this->grid->setModel($this->member,['member_no','sm_accounts','saving_and_current_accounts','non_active_accounts','name','PhoneNos','PermanentAddress']);
	}


	// function init(){
	// 	parent::init();

	// 	$till_date=$this->api->today;
	// 	if($_GET['to_date']){
	// 		$till_date=$_GET['to_date'];
	// 	}
	// 	$form=$this->add('Form');
	// 	$form->addField('DatePicker','from_date');
	// 	$form->addField('DatePicker','to_date');
	// 	$form->addField('dropdown','type')->setValueList(array('Recurring'=>'Recurring','DDS'=>'DDS','MIS'=>'MIS','FD'=>'FD','0'=>'All'));
	// 	$form->addSubmit('GET List');


	// 	$grid=$this->add('Grid_AccountsBase');
	// 	$grid->add('H3',null,'grid_buttons')->set('Deposit Member Insurance Report As On '. date('d-M-Y',strtotime($till_date))); 

	// 	$accounts_model=$this->add('Model_Account');
	// 	$accounts_model->add('Controller_Acl');
	// 	$accounts_model->setOrder('SchemeType,created_at');
	// 	$accounts_model->addCondition(
	// 			$accounts_model->dsql()->orExpr()
	// 				->where('SchemeType',ACCOUNT_TYPE_RECURRING)
	// 				->where('SchemeType',ACCOUNT_TYPE_FIXED)
	// 				->where('SchemeType',ACCOUNT_TYPE_DDS)
	// 		);	

	// 	$accounts_model->addCondition('DefaultAC',false);

	// 	if($_GET['filter']){
	// 		$this->api->stickyGET('filter');

	// 		if($_GET['from_date']){
	// 			$this->api->stickyGET('from_date');
	// 			$accounts_model->addCondition('created_at','>=',$_GET['from_date']);
	// 		}
	// 		if($_GET['to_date']){
	// 			$this->api->stickyGET('to_date');
	// 			$accounts_model->addCondition('created_at','<=',$_GET['to_date']);
	// 		}
	// 		if($_GET['type']){
	// 			$this->api->stickyGET('type');
	// 			$accounts_model->addCondition('account_type',$_GET['type']);
	// 		}

	// 	}else{
	// 		$accounts_model->addCondition('id',-1);
	// 	}

	// 	$accounts_model->addExpression('member_name')->set(function($m,$q){
	// 		return $m->refSQL('member_id')->fieldQuery('name');
	// 	})->sortable(true);

	// 	$accounts_model->addExpression('father_name')->set(function($m,$q){
	// 		return $m->refSQL('member_id')->fieldQuery('FatherName');
	// 	});

	// 	$accounts_model->addExpression('address')->set(function($m,$q){
	// 		return $m->refSQL('member_id')->fieldQuery('PermanentAddress');
	// 	});

	// 	$accounts_model->addExpression('age')->set(function($m,$q){
	// 		return $m->refSQL('member_id')->fieldQuery('DOB');
	// 	});


	// 	$accounts_model->addExpression('phone_nos')->set(function($m,$q){
	// 		return $m->refSQL('member_id')->fieldQuery('PhoneNos');
	// 	});

	// 	$grid->setModel($accounts_model,array('AccountNumber','scheme','member_name','father_name','address','phone_nos','age','Nominee','RelationWithNominee','Amount'));

	// 	$grid->addMethod('format_age',function($g,$f){
	// 		$age=array();
	// 		if($g->current_row[$f] !='0000-00-00 00:00:00'){
	// 			$age = $g->api->my_date_diff($g->api->today,$g->current_row[$f]?:$g->api->today);
	// 		}
	// 		$g->current_row[$f] = $g->current_row[$f]? $age['years']:"";
	// 	});

	// 	$grid->addFormatter('age','age');
	// 	$grid->addColumn('text','insurance_amount');

	// 	$paginator = $grid->addPaginator(50);
	// 	$grid->skip_var = $paginator->skip_var;

	// 	$grid->addSno();
	// 	// $grid->removeColumn('scheme');

	// 	// $js=array(
	// 	// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
	// 	// 	$this->js()->_selector('#header')->toggle(),
	// 	// 	$this->js()->_selector('#footer')->toggle(),
	// 	// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
	// 	// 	$this->js()->_selector('.atk-form')->toggle(),
	// 	// 	);

	// 	// $grid->js('click',$js);
	


	// 	if($form->isSubmitted()){
	// 		$send = array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'type'=>$form['type'],'filter'=>1);
	// 		$grid->js()->reload($send)->execute();

	// 	}	
	
	// }
}