<?php

class page_reports_deposit_duestoreceived extends Page {
	public $title="Dues To Received";
	function init(){
		parent::init();

		$loan_type_key=explode(",",LOAN_TYPES);
		$loan_type_value=explode(",",LOAN_TYPES);
		$loan_type=array_combine($loan_type_key,$loan_type_value);
		// foreach ($loan_type as $key => $value) {
		// 	$loan_type[$value]=$value;
		// }
		$loan_type['Recurring']="Recurring";
		// print_r($loan_type);
		// exit;

		$form=$this->add('Form');
		$form->addField('dropdown','account_type')->setValueList($loan_type)->setEmptyText("Please Select");
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');


		$premiums = $this->add('Model_Premium');
		$account_j=$premiums->join('accounts','account_id');

		$account_j->addField('DefaultAc');
		$account_j->addField('ActiveStatus');
		$account_j->addField('branch_id');
		$account_j->addField('account_type');

		$member_join=$account_j->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');
		$agent_join=$member_join->join('agents.member_id','id');
		$member_join=$agent_join->join('members','member_id');
		$member_join->addField('agent_name','name');
		$member_join->addField('agent_phoneno','PhoneNos');
		$premiums->addCondition('Paid',false);


		

		// $account=$this->add('Model_Account');
		// $account->addExpression('due_date')->set(function($m,$q){
		// 	return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".NumberOfPremiums MONTH)";
		// });

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('account_type');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			if($_GET['account_type'])
				$premiums->addCondition('account_type','like',$_GET['account_type']);
			if($_GET['from_date'])
				$premiums->addCondition('DueDate','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$premiums->addCondition('DueDate','<',$_GET['to_date']);
		}else
			$premiums->addCondition('id',-1);
		$grid->setModel($premiums,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','DueDate','Amount','agent_name','agent_phoneno','account_type','ActiveStatus'));
		$grid->addPaginator(50);
		$grid->addSno();
		// $grid->addColumn('expander','accounts');



		if($form->isSubmitted()){
			$grid->js()->reload(array('account_type'=>$form['account_type'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'],'filter'=>1))->execute();
		}

	}
}