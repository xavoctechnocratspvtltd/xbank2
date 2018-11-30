<?php


class page_reports_deposit_nonactiverdaccountholders extends Page {
	public $title = "Non Active RD Account Holders";

	function page_index(){
		
		$tabs = $this->add('Tabs');

		$tabs->addTabURL($this->app->url('./rd'),'RD');
		$tabs->addTabURL($this->app->url('./fd'),'FD');

	}

	function page_rd(){
		$members = $this->add('Model_Member');
		$members->addExpression('in_active_rd_account_count')->set(function($m,$q){
			return $m->add('Model_Account_Recurring')
						->addCondition('ActiveStatus',false)
						->addCondition('member_id',$q->getField('id'))
						->count();
		})->sortable(true);

		$members->addExpression('last_rd_account')->set(function($m,$q){
			return $m->add('Model_Account_Recurring')
						->addCondition('ActiveStatus',false)
						->addCondition('member_id',$q->getField('id'))
						->setOrder('created_at','desc')
						->setLimit(1)
						->fieldQuery('AccountNumber');
		});

		$members->addExpression('in_active_rd_amount_sum')->set(function($m,$q){
			return $m->add('Model_Account_Recurring')
						->addCondition('ActiveStatus',false)
						->addCondition('member_id',$q->getField('id'))
						->sum('Amount');
		})->sortable(true);

		$members->addExpression('active_rd_account_count')->set(function($m,$q){
			return $m->add('Model_Account_Recurring')
						->addCondition('ActiveStatus',true)
						->addCondition('member_id',$q->getField('id'))
						->count();
		});

		$members->addCondition('in_active_rd_account_count','>',0);
		$members->addCondition('active_rd_account_count',0);
		$members->addCondition('is_active',true);
		$members->addCondition('is_defaulter',false);

		$grid = $this->add('Grid');
		$grid->addSno();
		$grid->setModel($members,['name','member_no','PermanentAddress','PhoneNos','in_active_rd_account_count','last_rd_account','in_active_rd_amount_sum','active_rd_account_count']);


		$grid->removeColumn('active_rd_account_count');
		$grid->addQuickSearch(['name','PhoneNos']);
		$grid->addPaginator(500);
	}

	function page_fd(){
		$members = $this->add('Model_Member');
		$members->addExpression('in_active_fd_account_count')->set(function($m,$q){
			return $m->add('Model_Account_FixedAndMis')
						->addCondition('ActiveStatus',false)
						->addCondition('member_id',$q->getField('id'))
						->count();
		})->sortable(true);

		$members->addExpression('last_fd_account')->set(function($m,$q){
			return $m->add('Model_Account_FixedAndMis')
						->addCondition('ActiveStatus',false)
						->addCondition('member_id',$q->getField('id'))
						->setOrder('created_at','desc')
						->setLimit(1)
						->fieldQuery('AccountNumber');
		});

		$members->addExpression('in_active_fd_amount_sum')->set(function($m,$q){
			return $m->add('Model_Account_FixedAndMis')
						->addCondition('ActiveStatus',false)
						->addCondition('member_id',$q->getField('id'))
						->sum('Amount');
		})->sortable(true);

		$members->addExpression('active_fd_account_count')->set(function($m,$q){
			return $m->add('Model_Account_FixedAndMis')
						->addCondition('ActiveStatus',true)
						->addCondition('member_id',$q->getField('id'))
						->count();
		});

		$members->addCondition('in_active_fd_account_count','>',0);
		$members->addCondition('active_fd_account_count',0);
		$members->addCondition('is_active',true);
		$members->addCondition('is_defaulter',false);

		$grid = $this->add('Grid');
		$grid->addSno();
		$grid->setModel($members,['name','member_no','PermanentAddress','PhoneNos','in_active_fd_account_count','last_fd_account','in_active_fd_amount_sum','active_fd_account_count']);


		$grid->removeColumn('active_fd_account_count');
		$grid->addQuickSearch(['name','PhoneNos']);
		$grid->addPaginator(500);
	}

}