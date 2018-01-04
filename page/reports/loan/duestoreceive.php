<?php

class page_reports_loan_duestoreceive extends Page {

	public $title="Dues To Receive List";

	function init(){
		parent::init();

		$this->app->stickyGET('acc_type');
		$this->app->stickyGET('rep_mode');
		$this->app->stickyGET('filter');
		$this->app->stickyGET('dealer');


		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('Dealer');

		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addField('DropDown','account_type')->setEmptyText('All')->setValueList( array_combine(explode(",", LOAN_TYPES),explode(",", LOAN_TYPES)));
		$form->addField('DropDown','repayment_mode')->setEmptyText('All')->setValueList(array_combine(['Cash','Cheque','NACH/ECS'],['Cash','Cheque','NACH/ECS']));

		$form->addSubmit('Go');

		$grid = $this->add('Grid_AccountsBase');

		$from_date = $this->api->today;
		$to_date = $this->app->nextDate($this->api->today);
		
		if($this->app->stickyGET('from_date')){
			$from_date=$this->api->stickyGET('from_date');
		}

		if($this->app->stickyGET('to_date')){
			$to_date=$this->app->nextDate($this->api->stickyGET('to_date'));
		}



		$due_premiums = $this->add('Model_Premium');
		$account_j=$due_premiums->join('accounts','account_id');
		$member_j = $account_j->join('members','member_id');

		$account_j->addField('DefaultAc');
		$account_j->addField('AccountNumber');
		$account_j->addField('ActiveStatus');
		$account_j->addField('repayment_mode');
		$account_j->addField('account_type');
		$account_j->addField('branch_id');
		$account_j->hasOne('Agent','agent_id');
		$account_j->hasOne('Dealer','dealer_id');

		$scheme_j = $account_j->join('schemes','scheme_id');
		$scheme_j->addField('SchemeType');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$due_premiums->setOrder('SchemeType,AccountNumber');

		$due_premiums->addCondition('DueDate','>=',$from_date);
		$due_premiums->addCondition('DueDate','<',$to_date);
		// $due_premiums->addCondition('Paid',0);
		$due_premiums->addCondition('ActiveStatus',true);
		$due_premiums->addCondition('SchemeType','Loan');
		
		if($_GET['dealer'])
			$due_premiums->addCondition('dealer_id',$_GET['dealer']);

		if($act = $_GET['acc_type']){						
			$due_premiums->addCondition('account_type',$act);
		}

		if($repm = $_GET['rep_mode']){
			$due_premiums->addCondition('repayment_mode',$repm);
		}

		$due_premiums->add('Controller_Acl');

		$grid->setModel($due_premiums,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos', 'Amount','DueDate','agent','dealer','dealer_id','repayment_mode','account_type'));
		$grid->addSno();
		$grid->addPaginator(500);

		$grid->addTotals(array('Amount'));

		if($form->isSubmitted()){			
			$grid->js()->reload(['from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'acc_type'=>$form['account_type'],'rep_mode'=>$form['repayment_mode'],'dealer'=>$form['dealer'], 'filer'=>1])->execute();
		}

	}
}