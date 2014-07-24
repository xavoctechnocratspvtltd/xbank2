<?php
class page_reports_loan_accountdetailed extends Page {
	public $title="Loan Account Detailed Report";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$accounts_no_field=$form->addField('autocomplete/Basic','accounts_no');
		$accounts=$this->add('Model_Account_Loan');
		$accounts_no_field->setModel($accounts);

		$form->addSubmit('GET List');


		$grid=$this->add('Grid'); 

		$accounts_model=$this->add('Model_Account_Loan');

		if($_GET['accounts_no']){

			//TODO

		}

		$grid->setModel($accounts_model);

		$grid->addPaginator(50);


		if($form->isSubmitted()){
			$grid->js()->reload(array('accounts_no'=>$form['accounts_no']))->execute();

		}	
	

	}
}