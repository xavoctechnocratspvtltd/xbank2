<?php

class page_reports_loan_emiduelist extends Page {
	public $title="EMI Due List (Recovery List)";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','hardlist'=>'Hard List','npa'=>'NPA List','time_collapse'=>'Time Collaps'));
		$form->addField('dropdown','loan_type')->setValueList(array('vl'=>'VL','pl'=>'PL','other'=>'Other','all'=>'All'));
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
		$form->addField('CheckBox',$document['name']);
		}
		$form->addSubmit('GET List');

		$grid=$this->add('Grid'); 

		$account_model=$this->add('Model_Active_Account_Loan');
		$member_join=$account_model->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');

		$account_model->addCondition('DefaultAC',false);
		$account_model->addCondition('MaturedStatus',false);

		$account_model->addExpression('paid_premium_count')->set(function($m,$q){
			return $m->refSQL('Premium')
						->addCondition('PaidOn','<>',null)
						->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
						->addCondition('DueDate','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today))
						->count();
		})->sortable(true);

		$account_model->addExpression('due_premium_count')->set(function($m,$q){
			return $m->refSQL('Premium')
						->addCondition('PaidOn',null)
						->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
						->addCondition('DueDate','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today))
						->count();
		});

		$account_model->addExpression('last_premium')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('DueDate');
		});
		

		$account_model->addCondition('last_premium','>=',$this->api->today);
		
		if($_GET['filter']){

			if($_GET['dealer'])
				$account_model->addCondition('dealer_id',$_GET['dealer']);

			switch ($_GET['report_type']) {
				case 'duelist':
					$account_model->addCondition('due_premium_count','>',0);
					$account_model->addCondition('due_premium_count','<=',2);
					break;
				
				default:
					# code...
					break;
			}

		}

		$grid->setModel($account_model->debug(),array('AccountNumber','created_at','scheme','member_name','FatherName','PhoneNos','PermanentAddress','paid_premium_count','due_premium_count','last_premium'));
		$grid->addPaginator(50);


		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'], 'loan_type'=>$form['loan'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}		


	}
}