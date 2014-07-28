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

		$grid=$this->add('Grid'); 

		$accounts_model=$this->add('Model_Active_Account_Loan');

		$accounts_model->addExpression('insurance_month')->set('(MONTH(LoanInsurranceDate))');
		$accounts_model->addExpression('insurance_date')->set('(DAY(LoanInsurranceDate))');

		if($_GET['filter']){

			if($_GET['from_date']){
				$accounts_model->addCondition('insurance_month','>=',(int) date('m',strtotime($_GET['from_date'])));
				$accounts_model->addCondition('insurance_date','>=',(int)date('d',strtotime($_GET['from_date'])));
				
			}

			if($_GET['to_date']){
				$accounts_model->addCondition('insurance_month','<=',(int)date('m',strtotime($_GET['to_date'])));
				$accounts_model->addCondition('insurance_date','<=',(int)date('d',strtotime($_GET['to_date'])));
				$accounts_model->addCondition('maturity_date','<=',$this->api->nextDate($_GET['to_date']));
			}
			
			if($_GET['dealer'])
				$accounts_model->addCondition('dealer_id',$_GET['dealer']);

		}

		$accounts_model->setOrder('id','desc');
		$accounts_model->addCondition('DefaultAC',false);
		$accounts_model->addCondition('AccountNumber','like','%vl%');
		
		$accounts_model->add('Controller_Acl');

		$grid->setModel($accounts_model,array('AccountNumber','LoanInsurranceDate','dealer','insurance_month','insurance_date','maturity_date'));

		$grid->addPaginator(50);
		$grid->removeColumn('insurance_month');
		$grid->removeColumn('insurance_date');

		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1);
			
			$grid->js()->reload($send)->execute();

		}	

	}
}