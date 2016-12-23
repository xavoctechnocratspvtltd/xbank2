<?php

class page_reports_deposit_advancecheqpayment extends Page {
	public $title="Advance Cheque Payment Given";
	
	function init(){
		parent::init();


		$till_date = $this->api->today;

		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');

		$account_type_array=array('%'=>'All','DDS'=>'DDS','FD'=>'Fixed Account','MIS'=>'MIS','Recurring'=>'Recurring');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','account_type')->setValueList($account_type_array);
		
		$document=$this->add('Model_Document');
		$document->addCondition('FixedMISAccount',true);
		$document->addCondition('RDandDDSAccount',true);

		$document_field=$form->addField('dropdown','document')->setEmptyText('All Documents');
		$document_field->setModel($document);

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Advanced Cheque Payment Reports'); 

		$account=$this->add('Model_Account');
		$member_join = $account->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('phone_no','PhoneNos');

		$agent_join=$account->leftJoin('agents','agent_id');
		$agen_member_join=$agent_join->leftJoin('members','member_id',null,'agm');
		$agen_member_join->addField('agent_name','name');
		$agen_member_join->addField('agent_phoneno','PhoneNos');

		$document_submitted_join=$account->join('documents_submitted.accounts_id');
		$document_submitted_join->addField('documents_id');
		$document_submitted_join->addField('Description');

		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("account_type");

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$account->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$account->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

			if($_GET['agent'])
				$account->addCondition('agent_id',$_GET['agent']);

			if($_GET['account_type']){
				if($_GET['account_type']=='%')
					$account->addCondition('account_type',$account_type_array);
				else
					$account->addCondition('account_type','like',$_GET['account_type']);
			}

			if($this->api->stickyGET('document')){
				$account->addCondition('documents_id',$_GET['document']);
			}

		}else
			$account->addCondition('id',-1);

		$account->addCondition('CurrentBalanceCr','>',0);
		$account->add('Controller_Acl');
		$account->setOrder('created_at','desc');

		$account->addExpression('maturity_date')->set(function($m,$q){
			return "(IF (".$q->getField('account_type')."='FD' OR ".$q->getField('account_type')."='MIS',(
					DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod + 1) DAY)
				),(
				DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod + 1) MONTH)
				)
				)
				)";
		});

		$grid->setModel($account,array('AccountNumber','member_name','phone_no','scheme','Amount','maturity_date','Description','agent_name','agent_code','agent_phoneno','created_at'));
		$grid->addPaginator(50);
		$grid->addSno();

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){
			$grid->js()->reload(array('account_type'=>$form['account_type'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'agent'=>$form['agent']?:0,'document'=>$form['document'],'filter'=>1))->execute();
		}
	}
}