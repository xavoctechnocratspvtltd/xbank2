<?php
class page_utility_activemember extends Page{

	public $title= 'Active Memebr';

	function init(){
		parent::init();

		$financial_year = $this->app->getFinancialYear();
		
		$start_0 = $financial_year['start_date'];
		$end_0 = $financial_year['end_date'];

		$start_1 = $this->app->previousYear($start_0);
		$end_1 = $this->app->previousYear($end_0);

		$start_2 = $this->app->previousYear($start_1);
		$end_2 = $this->app->previousYear($end_1);

		$start_3 = $this->app->previousYear($start_2);
		$end_3 = $this->app->previousYear($end_2);

		$start_4 = $this->app->previousYear($start_3);
		$end_4 = $this->app->previousYear($end_3);

		$start_5 = $this->app->previousYear($start_4);
		$end_5 = $this->app->previousYear($end_4);

		$form = $this->add('Form');
		$form->addField('DropDown','active_from_last_years')->setValueList(['1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5']);
		$form->addSubmit('Get List');


		$model= $this->add('Model_Member');
		
		$model->addExpression('accounts_in_0_years')->set(function($m,$q)use($start_0,$end_0){
			return $m->add('Model_Account')
					->addCondition('member_id',$q->getField('id'))
					->addCondition('account_type',['FD','MIS',ACCOUNT_TYPE_DDS,ACCOUNT_TYPE_RECURRING])
					->addCondition('created_at','>=',$start_0)
					->addCondition('created_at','<',$this->app->nextDate($end_0))
					->count();
		});

		$model->addExpression('accounts_in_1_years')->set(function($m,$q)use($start_1,$end_1){
			return $m->add('Model_Account')
					->addCondition('member_id',$q->getField('id'))
					->addCondition('account_type',['FD','MIS',ACCOUNT_TYPE_DDS,ACCOUNT_TYPE_RECURRING])
					->addCondition('created_at','>=',$start_1)
					->addCondition('created_at','<',$this->app->nextDate($end_1))
					->count();
		});

		$model->addExpression('accounts_in_2_years')->set(function($m,$q)use($start_2,$end_2){
			return $m->add('Model_Account')
					->addCondition('member_id',$q->getField('id'))
					->addCondition('account_type',['FD','MIS',ACCOUNT_TYPE_DDS,ACCOUNT_TYPE_RECURRING])
					->addCondition('created_at','>=',$start_2)
					->addCondition('created_at','<',$this->app->nextDate($end_2))
					->count();
		});

		$model->addExpression('accounts_in_3_years')->set(function($m,$q)use($start_3,$end_3){
			return $m->add('Model_Account')
					->addCondition('member_id',$q->getField('id'))
					->addCondition('account_type',['FD','MIS',ACCOUNT_TYPE_DDS,ACCOUNT_TYPE_RECURRING])
					->addCondition('created_at','>=',$start_3)
					->addCondition('created_at','<',$this->app->nextDate($end_3))
					->count();
		});

		$model->addExpression('accounts_in_4_years')->set(function($m,$q)use($start_4,$end_4){
			return $m->add('Model_Account')
					->addCondition('member_id',$q->getField('id'))
					->addCondition('account_type',['FD','MIS',ACCOUNT_TYPE_DDS,ACCOUNT_TYPE_RECURRING])
					->addCondition('created_at','>=',$start_4)
					->addCondition('created_at','<',$this->app->nextDate($end_4))
					->count();
		});

		$model->addExpression('accounts_in_5_years')->set(function($m,$q)use($start_5,$end_5){
			return $m->add('Model_Account')
					->addCondition('member_id',$q->getField('id'))
					->addCondition('account_type',['FD','MIS',ACCOUNT_TYPE_DDS,ACCOUNT_TYPE_RECURRING])
					->addCondition('created_at','>=',$start_5)
					->addCondition('created_at','<',$this->app->nextDate($end_5))
					->count();
		});

		if($_GET['active_from_last_years']){
			for ($i=1; $i <= $_GET['active_from_last_years']; $i++) { 
				$model->addCondition('accounts_in_'.$i.'_years','>',0);
			}
		}

		$model->setOrder('id','desc');

		$grid = $this->add('Grid');
		$grid->setModel($model,['member_name','PhoneNos','FatherName','PermanentAddress','tehsil','district']);

		$grid->addPaginator(500);

		if($form->isSubmitted()){
			$grid->js()->reload(['active_from_last_years'=>$form['active_from_last_years']])->execute();
		}
	}
}