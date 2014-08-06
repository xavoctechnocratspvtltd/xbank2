<?php

class page_reports_loan_dispatch extends Page {
	public $title="Loan Dispatch Report";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('DropDown','document')->setEmptyText('No Document')->setModel('Document')->addCondition('LoanAccount',true);
		$form->addSubmit('GET List');

		$grid=$this->add('Grid'); 

		$account_model=$this->add('Model_Account_Loan');

		$member_join = $account_model->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('CurrentAddress');
		$member_join->addField('PhoneNos');
		
		$account_model->addExpression('no_of_emi')->set(function($m,$q){
			return $m->refSQL('Premium')->count();
		});

		$account_model->addExpression('emi')->set(function($m,$q){
			return $m->refSQL('Premium')->setLimit(1)->fieldQuery('Amount');
		});

		$grid_array = array('AccountNumber','created_at','member','FatherName','CurrentAddress','scheme','PhoneNos','no_of_emi','emi');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			
			if($doc_id = $_GET['document']){
				$this->api->stickyGET('document');

				$document_model = $this->add('Model_Document')->load($_GET['document']);

				$account_model->addExpression($this->api->normalizeName($document_model['name']))->set(function($m,$q)use($doc_id){
					return $m->refSQL('DocumentSubmitted')
							->addCondition('documents_id',$doc_id)
							->addCondition('accounts_id',$q->getField('id'))
							->fieldQuery('Description');
				});
				$grid_array[] = $this->api->normalizeName($document_model['name']);
			}

			if($_GET['dealer']){
				$this->api->stickyGET('dealer');
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			if($_GET['from_date']){
				$this->api->stickyGET('from_date');
				$account_model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET('to_date');
				$account_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

		}

		$account_model->addCondition('DefaultAC',false);

		$grid->setModel($account_model,$grid_array);

		$grid->addMethod('format_myTotal',function($grid, $field){
			$grid->current_row[$field] = $grid->current_row['no_of_emi'] * $grid->current_row['emi'];
		});

		$grid->addColumn('myTotal','total');


		$grid->addPaginator(50);


		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'document'=>$form['document']?:0,'filter'=>1);
			$grid->js()->reload($send)->execute();

		}		


	}
}