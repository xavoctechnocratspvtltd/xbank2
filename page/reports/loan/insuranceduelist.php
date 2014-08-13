<?php
class page_reports_loan_insuranceduelist extends Page {
	public $title="Insurance Due List";
	function init(){
		parent::init();


		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase'); 

		$accounts_model=$this->add('Model_Active_Account_Loan');
		$member_join=$accounts_model->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');

		$accounts_model->addExpression('insurance_month')->set('(MONTH(LoanInsurranceDate))');
		$accounts_model->addExpression('insurance_date')->set('(DAY(LoanInsurranceDate))');

		if($_GET['filter']){

			if($_GET['from_date']){
				$accounts_model->addCondition('insurance_month','>=',(int) date('m',strtotime($_GET['from_date'])));
				$accounts_model->addCondition('insurance_date','>=',(int)date('d',strtotime($_GET['from_date'])));
				$accounts_model->addCondition('maturity_date','>=',$this->api->nextDate($_GET['from_date']));
				
			}

			if($_GET['to_date']){
				$accounts_model->addCondition('insurance_month','<=',(int)date('m',strtotime($_GET['to_date'])));
				$accounts_model->addCondition('insurance_date','<=',(int)date('d',strtotime($_GET['to_date'])));
			}
			
			if($_GET['dealer'])
				$accounts_model->addCondition('dealer_id',$_GET['dealer']);

		}else
			$accounts_model->addCondition('id',-1);

		$accounts_model->setOrder('id','desc');
		$accounts_model->addCondition('DefaultAC',false);
		$accounts_model->addCondition('AccountNumber','like','%vl%');
		
		$accounts_model->add('Controller_Acl');

		$accounts_model->getElement('LoanInsurranceDate')->caption('Insurance Due Date');


		$grid->setModel($accounts_model,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','LoanInsurranceDate','dealer','insurance_month','insurance_date','maturity_date'));

		$grid->addMethod('format_onlyDateMonth',function($g,$f){
			$g->current_row[$f] = date('d-M',strtotime($g->current_row[$f]));
		});

		$grid->addFormatter('LoanInsurranceDate','onlyDateMonth');

		$grid->addPaginator(50);
		$grid->addSno();
		$grid->removeColumn('insurance_month');
		$grid->removeColumn('insurance_date');

		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1);
			
			$grid->js()->reload($send)->execute();

		}	

	}
}