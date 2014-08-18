<?php

class page_reports_deposit_duestogive extends Page {
	public $title="Dues To Give";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('dropdown','account_type')->setValueList(array('all'=>'All','rd'=>'RD','fd'=>'FD','dds'=>'DDS','mis'=>'MIS'));
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');


		$account=$this->add('Model_Account');
		$member_join=$account->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');
		$agent_join=$member_join->join('agents.member_id','id');
		$member_join=$agent_join->join('members','member_id');
		$member_join->addField('agent_name','name');
		$member_join->addField('agent_phoneno','PhoneNos');
		$account->addExpression('due_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$m->dsql()->getField('created_at')."), INTERVAL +".$m->scheme_join->table_alias.".NumberOfPremiums MONTH)";
		});

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('account_type');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			if($_GET['account_type'])
				$account->addCondition('account_type',$_GET['account_type']);
			if($_GET['from_date'])
				$account->addCondition('due_date','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$account->addCondition('due_date','<=',$_GET['to_date']);
		}else
			$account->addCondition('id',-1);
		$grid->setModel($account,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','due_date','Amount','agent_name','agent_phoneno','ActiveStatus'));
		$grid->addPaginator(50);
		$grid->addSno();
		$grid->addColumn('expander','accounts');



		if($form->isSubmitted()){
			$grid->js()->reload(array('account_type'=>$form['account_type'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'],'filter'=>1))->execute();
		}

	}
}