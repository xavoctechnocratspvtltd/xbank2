<?php

class page_reports_member_smaudit extends Page {
	public $title = "SM Audit";

	function init(){
		parent::init();

		$this->member = $this->add('Model_Member');
		$this->member->addExpression('sm_count')->set(function($m,$q){
			return  $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])->addCondition('member_id',$q->getField('id'))->count();
		});
		$this->grid = $this->add('Grid');
		$this->grid->addSno();
		$this->grid->addPaginator(500);
	}

	function page_index(){
		$this->grid->destroy();
		$tabs = $this->add('Tabs');
		$tabs->addTabURL($this->app->url('./nosm'),'Members Without SM');
		$tabs->addTabURL($this->app->url('./smwithzerobalance'),'Members With Zero balance SM');
		$tabs->addTabURL($this->app->url('./multiplesm'),'Members With multiple SM');
	}

	function page_nosm(){

		$this->member->addCondition('sm_count',0);

		$this->grid->setModel($this->member,['name','member_no']);
	}

	function page_smwithzerobalance(){
		// $this->member->addExpression('sm_accounts')->set(function($m,$q){
		// 	$smacc= $this->add('Model_Account_SM',['table_alias'=>'sm_accounts','with_balance_cr'=>true])
		// 		->addCondition('member_id',$q->getField('id'));
		// 	$smacc->addCondition($q->expr('[0]=0',[$smacc->getElement('tra_cr')]));
		// 	return $smacc->_dsql()->del('fields')
		// 		->field('GROUP_CONCAT(AccountNumber)');
		// });
		// $this->grid->setModel($this->member,['name','sm_accounts']);
		// $this->grid->addPaginator(10);
		$this->grid->destroy();
		$sm_accounts = $this->add('Model_Account_SM',['with_balance_dr'=>true]);

		$sm_accounts->addCondition('balance_dr',0);
		// $sm_accounts->addCondition('tra_dr',0);

		$grid = $this->add('Grid');
		$grid->addSno();
		$grid->setModel($sm_accounts,['member_name_only','AccountNumber','balance_cr','balance_dr']);
		$grid->addPaginator(100);
	}

	function page_multiplesm(){
		
		$this->member->addExpression('sm_accounts')->set(function($m,$q){
			return $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])
				->addCondition('member_id',$q->getField('id'))
				->addCondition('ActiveStatus',true)
				->_dsql()->del('fields')
				->field('GROUP_CONCAT(AccountNumber)');
		});

		$this->member->addCondition('sm_count','>',1);
		$this->grid->setModel($this->member,['name','member_no','sm_count','sm_accounts']);
	}

}