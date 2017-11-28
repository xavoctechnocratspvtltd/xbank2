<?php

class page_reports_general_document extends Page {
	public $title='Document Report';

	function init(){
		parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}
		$form=$this->add('Form');
		// $form->setModel('DocumentSubmitted');

		$form->addField('autocomplete/Basic','dealer')->setModel('ActiveDealer');
		$form->addField('autocomplete/Basic','account')->setModel('Account');
		$form->addField('autocomplete/Basic','member')->setModel('Member');
		$form->addField('autocomplete/Basic','agent')->setModel('Agent');
		$form->addField('autocomplete/Basic','agent_guarantor')->setModel('AgentGuarantor');
		$form->addField('autocomplete/Basic','dsa')->setModel('DSA');
		$form->addField('autocomplete/Basic','dsa_guarantor')->setModel('DSAGuarantor');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		
		$document=$this->add('Model_Document');
		// $document->addCondition('LoanAccount',true);
		$document_field=$form->addField('dropdown','document')->setEmptyText('All Documents');
		$document_field->setModel($document);
		$form->addField('line','value');

		$form->addSubmit('GET List');
		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Genral Documents As On '. date('d-M-Y',strtotime($till_date))); 
		$document_submitted_model=$this->add('Model_DocumentSubmitted');
		$document_join = $document_submitted_model->join('documents','documents_id');
		$account_join = $document_submitted_model->leftJoin('accounts','accounts_id');
		$account_join->addField('account_dealer_id','dealer_id');
		$dealer_join = $account_join->leftJoin('dealers','dealer_id');

		$account_join->addField('branch_id');
		$account_join->addField('DefaultAC');
		// $dealer_join->addField('dealer_name','name');


		if($_GET['filter']){
			$this->api->stickyGET("filter");

			if($_GET['dealer']){
				$this->api->stickyGET("dealer");
				$document_submitted_model->addCondition('account_dealer_id',$_GET['dealer']);
			}

			if($_GET['member']){
				$this->api->stickyGET("dealer");
				$document_submitted_model->addCondition('member_id',$_GET['member']);
			}

			if($_GET['agent']){
				$this->api->stickyGET("agent");
				$document_submitted_model->addCondition('agent_id',$_GET['agent']);
			}

			if($_GET['account']){
				$this->api->stickyGET("account");
				$document_submitted_model->addCondition('accounts_id',$_GET['account']);
			}

			if($_GET['agent_guarantor']){
				$this->api->stickyGET("agent_guarantor");
				$document_submitted_model->addCondition('agentguarantor_id',$_GET['agent_guarantor']);
			}

			if($_GET['dsa']){
				$this->api->stickyGET("dsa");
				$document_submitted_model->addCondition('dsa_id',$_GET['dsa']);
			}

			if($_GET['dsa_guarantor']){
				$this->api->stickyGET("dsa_guarantor");
				$document_submitted_model->addCondition('dsaguarantor_id',$_GET['dsa_guarantor']);
			}





			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$document_submitted_model->addCondition('submitted_on','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$document_submitted_model->addCondition('submitted_on','<',$this->api->nextDate($_GET['to_date']));
			}

			if($_GET['value']){
				$this->api->stickyGET("value");
				$document_submitted_model->addCondition('Description','like','%'.$_GET['value'].'%');
			}

			if($_GET['document']){
				$this->api->stickyGET("document");
				$document_submitted_model->addCondition('documents_id',$_GET['document']);
			}



		}

		
		$document_submitted_model->addCondition([['DefaultAC',false],['DefaultAC',null]]);
		$document_submitted_model->setOrder('submitted_on desc,id desc,accounts desc');
		// $document_submitted_model->add('Controller_Acl');

		$grid->setModel($document_submitted_model);

		$grid->addFormatter('Description','wrap');
		$grid->addPaginator(500);

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){
			$send = array(
				'from_date'=>$form['from_date']?:0,
				'to_date'=>$form['to_date']?:0,
				'document'=>$form['document'],
				'value'=>$form['value'],
				'dealer'=>$form['dealer'],
				'member'=>$form['member'],
				'agent'=>$form['agent'],
				'account'=>$form['account'],
				'agent_guarantor'=>$form['agent_guarantor'],
				'dsa'=>$form['dsa'],
				'dsa_guarantor'=>$form['dsa_guarantor'],

				'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	


	}
}