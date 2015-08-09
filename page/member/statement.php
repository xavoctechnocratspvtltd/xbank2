<?php

class page_member_statement extends Page {
	public $title = "Member Statement";

	function init(){
		parent::init();
		
		$account_id=$_GET['account_id'];
		$member_id=$_GET['member_id'];
		// throw new Exception($member_id, 1);
		$this->api->stickyGET('member_id');
		$this->api->stickyGET('account_id');
		$this->api->stickyGET('from_date');
		$this->api->stickyGET('to_date');
		
		$form=$this->add('Form');
		$form->addField('hidden','account')->set($account_id);
		$form->addField('hidden','member')->set($member_id);
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('Get Statement');

		$grid = $this->add('Grid_AccountStatement');
		$transactions_row = $this->add('Model_TransactionRow');

		$transaction_j = $transactions_row->join('transactions','transaction_id');
		$account_j = $transactions_row->join('accounts','account_id');
		// $account_j->addField('id','account_id');
		$account_j->addField('member_id');

		$transactions_row->addCondition('account_id',$_GET['account_id']);
		$transactions_row->addCondition('member_id',$_GET['member_id']);

		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			if($_GET['account_id']){
				$transactions_row->addCondition('account_id',$_GET['account_id']);
			}
			if($_GET['from_date']){
				$transactions_row->addCondition('created_at','>=',$_GET['from_date']);
			}
			if($_GET['to_date']){
				$transactions_row->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}
		}else{
			$transactions_row->addCondition('id',-1);
		}

		$transactions_row->setOrder('created_at');
		$grid->setModel($transactions_row,array('account','created_at','Narration','amountDr','amountCr'));
		// $grid->addPaginator(20);

		$grid->addSno();

		if($form->isSubmitted()){
			$grid->js()->reload(
					array(
						'account_id'=>$form['account'],
						'member_id'=>$form['member'],
						'from_date'=>$form['from_date']?:0,
						'to_date'=>$form['to_date']?:0,
						'filter'=>1
						)
					)->execute();
		}

	}
}