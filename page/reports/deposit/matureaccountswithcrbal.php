<?php

class page_reports_deposit_matureaccountswithcrbal extends Page {
	public $title="Matured Accounts With Creedit Balance";
	
	function init(){
		parent::init();

		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		// $form->addField('DatePicker','as_on_date')->validateNotNull();

		$account_type_array=array('%'=>'All','DDS'=>'DDS','FD'=>'Fixed Account','MIS'=>'MIS','Recurring'=>'Recurring');

		$form->addField('dropdown','account_type')->setValueList($account_type_array);
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
		$account_model->addExpression('agent_phone')->set($this->add('Model_Member',array('table_alias'=>'agent_phone'))->addCondition('id',$account_model->refSQL('agent_id') ->fieldQuery('member_id'))->fieldQuery('PhoneNos'));

		if($this->api->stickyGET('filter')){
			
			if($this->api->stickyGET('agent')){
				$account_model->addCondition('agent_id',$_GET['agent']);
			}

			if($this->api->stickyGET('account_type')){
				if($_GET['account_type'] != '%')
					$account_model->addCondition('account_type',$_GET['account_type']);
				else
					$account_model->addCondition('account_type',$account_type_array);
			}

		}else{
			$account_model->addCondition('id',-1);
		}

		$account_model->addExpression('maturity_date')->set(function($m,$q){
			return "(IF (".$q->getField('account_type')."='FD' OR ".$q->getField('account_type')."='MIS',(
					DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod + 1) DAY)
				),(
				DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod + 1) MONTH)
				)
				)
				)";
		});

		$account_model->addExpression('has_balance')->set('CurrentBalanceCr - CurrentBalanceDr')->caption('Balance')->sortable(true);
		$account_model->addCondition('has_balance','<>',0);
		$account_model->addCondition('DefaultAC',false);
		
		$account_model->_dsql()->having('maturity_date','<',$this->api->today);

		$account_model->add('Controller_Acl');

		$grid = $this->add('Grid_AccountsBase');
		$grid->setModel($account_model,array('member_detail','AccountNumber','phone_no','SchemeType','has_balance','maturity_date','agent','agent_phone','ActiveStatus'));
		$grid->addSno();
		$grid->addPaginator(50);
		$grid->addFormatter('agent','wrap');

		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'as_on_date'=>$form['as_on_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}
}