<?php

class page_reports_deposit_duestogive extends Page {
	public $title="Dues To Give";
	
	function init(){
		parent::init();

		$mo = $this->app->stickyGET('mo');

		$till_date = $this->api->today;

		if($this->app->stickyGET('to_date')){
			$till_date=$_GET['to_date'];
		}
		$account_type_array=array('%'=>'All','DDS'=>'DDS','FD'=>'Fixed Account','MIS'=>'MIS','Recurring'=>'Recurring');
		
		$form=$this->add('Form');
		$field_mo = $form->addField('autocomplete/Basic','mo');
		$field_mo->setModel('Mo');
		$field_mem = $form->addField('autocomplete/Basic','member');
		$field_mem->setModel('Member');
		$field_agent = $form->addField('autocomplete/Basic','agent');
		$field_agent->setModel('Agent');

		$form->addField('dropdown','account_type')->setValueList($account_type_array);
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
			
		if($this->app->auth->model->isCEO() OR $this->app->auth->model->isSuper()){
			$form->addField('dropdown','branch_id')->setEmptyText('All')->setModel('Branch');
		}

		$document=$this->add('Model_Document');
		$document->depositDocuments();

		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}


		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Dues To Give Report From ' . $_GET['from_date']. ' to ' . $till_date );

		$account=$this->add('Model_Account');
		$member_join=$account->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');
		
		$agent_join=$account->leftJoin('agents','agent_id');
		$agen_member_join=$agent_join->leftJoin('members','member_id');
		$agen_member_join->addField('agent_name','name');
		$agen_member_join->addField('agent_phoneno','PhoneNos');

		
		$account->addExpression('Loan_AccountNumber')->set(function($m,$q){
			$loan_m = $m->add('Model_Account',['table_alias'=>'loan_acc']);
			$loan_m->addCondition('LoanAgainstAccount_id',$q->getField('id'));
			$loan_m->addCondition('ActiveStatus',true);
			return $loan_m->_dsql()->del('fields')->field('GROUP_CONCAT(AccountNumber)');
		});

		$account->addExpression('maturity_date')->set(function($m,$q){
			return "(IF (".$q->getField('account_type')."='FD' OR ".$q->getField('account_type')."='MIS',(
					DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod + 0) DAY)
				),(
				DATE_ADD(DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod) MONTH), INTERVAL + 0 DAY)
				)
				)
				)";
		});


		// after confirm implement this becase when selecting checkbox from filter form it gives document
		// $document_submitted_join=$account->join('documents_submitted.accounts_id');
		// $document_submitted_join->addField('documents_id');
		// $document_submitted_join->addField('Description');

		$account->addExpression('agent_mo_id')->set($account->refSQL('agent_id')->fieldQuery('mo_id'));
		$account->addExpression('agent_mo_name')->set($account->refSQL('agent_id')->fieldQuery('mo'))->caption('Mo');

		$grid_column_array=array('branch','AccountNumber','Loan_AccountNumber','scheme','member_name','FatherName','PermanentAddress','PhoneNos','maturity_date','Amount','MaturityAmount','agent_mo_name','agent_name','agent_phoneno','ActiveStatus','account_type');


		if($_GET['filter']){			
			$this->api->stickyGET('filter');
			$this->api->stickyGET('account_type');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			$this->api->stickyGET('branch_id');
			$this->api->stickyGET('member');
			$this->api->stickyGET('agent');
			if($_GET['account_type']){
				if($_GET['account_type']=='%')
					$account->addCondition('account_type',array_keys($account_type_array));
				else
					$account->addCondition('account_type','like',$_GET['account_type']);
			}

			if($_GET['mo']){
				$account->addCondition('agent_mo_id',$_GET['mo']);
			}

			if($_GET['member']){
				$account->addCondition('member_id',$_GET['member']);
			}

			if($_GET['agent']){
				$account->addCondition('agent_id',$_GET['agent']);
			}

			if($_GET['from_date'])
				$account->_dsql()->having('maturity_date','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$account->_dsql()->having('maturity_date','<=',$_GET['to_date']);

			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($this->api->stickyGET('doc_'.$document->id)){
					$this->api->stickyGET('doc_'.$document->id);
					$account->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}
			
			if($_GET['branch_id'] && $_GET['branch_id'] !=='null'){
				$account->addCondition('branch_id',$_GET['branch_id']);
			}
		}else{			
			$account->addCondition('id',-1);
		}

		$account->addCondition('DefaultAC',false);
		
		if(!$this->app->auth->model->isSuper()){
			$account->add('Controller_Acl');
		}

		$account->addExpression('MaturityAmount',function($m,$q){
			return $q->expr('IF([0]="MIS",[1],(CurrentBalanceCr + CurrentInterest - CurrentBalanceDr))',[$m->getElement('account_type'),$m->getElement('Amount')]);
		});

		$grid->setModel($account,$grid_column_array);
		$grid->addPaginator(500);
		$grid->addSno();
		$grid->addFormatter('PermanentAddress','wrap');
		$grid->addTotals(['Amount','MaturityAmount']);
		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);


		if($form->isSubmitted()){
			
			$send = array('account_type'=>$form['account_type'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'],'branch_id'=>$form['branch_id'],'mo'=>$form['mo'],'member'=>$form['member'],'agent'=>$form['agent'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			// throw new \Exception(print_r($send,true), 1);
			
			$grid->js()->reload($send)->execute();

			// $grid->js()->reload(array('account_type'=>$form['account_type'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'],'filter'=>1))->execute();
		}

	}
}