<?php

class page_reports_deposit_matureaccountswithcrbal extends Page {
	public $title="Matured Accounts With Creedit Balance";
	
	function init(){
		parent::init();

		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','as_on_date');

		$array2=explode(',', ACCOUNT_TYPES);
		$account_type_array=array('%'=>'All');
		foreach ($array2 as $act) {
			$account_type_array[$act] =$act;
		}

		$form->addField('dropdown','account_type')->setValueList($account_type_array)->setEmptyText('Please Select');
		$form->addSubmit('GET List');

		$account_model = $this->add('Model_Account');
		
		$qac= $account_model->dsql();

		$m= $this->add('Model_Member',array('table_alias'=>'md'));

		$account_model->addExpression('member_detail')->set(
			$qac->concat(
				$m->addCondition('id',$account_model->getElement('member_id'))->fieldQuery('name'),
				' :: ',
				$m->addCondition('id',$account_model->getElement('member_id'))->fieldQuery('id')
			)
		);

		$account_model->addExpression('phone_no')->set($account_model->refSQL('member_id')->fieldQuery('PhoneNos'));
		$account_model->addExpression('maturity_date')->set("'Maturity Date'");
		$account_model->addExpression('agent_phone')->set($this->add('Model_Member',array('table_alias'=>'agent_phone'))->addCondition('id',$account_model->refSQL('agent_id') ->fieldQuery('member_id'))->fieldQuery('PhoneNos'));

		if($_GET['filter']){
			
			if($_GET['agent']){
				$account_model->addCondition('agent_id',$_GET['agent']);
			}

			if($_GET['account_type']){
				$account_model->addCondition('SchemeType',$_GET['account_type']);
			}

		}

		$account_model->addExpression('has_balance')->set('CurrentBalanceCr - CurrentBalanceDr');
		$account_model->addCondition('has_balance','<>',0);

		$grid = $this->add('Grid_AccountsBase');
		$grid->setModel($account_model,array('AccountNumber','member_detail','phone_no','SchemeType','Amount','maturity_date','agent','agent_phone'));
		$grid->addSno();
		$grid->addPaginator(50);

		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'as_on_date'=>$form['as_on_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}
}