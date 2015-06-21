<?php

class page_reports_loan_forclose extends Page {
	public $title="For Close Repots";
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$loan_accoun = $form->addField('autocomplete/Basic','account_no')->validateNotNull();
		$loan_accoun->setModel('Account_Loan');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('For Close Report'); 

		$account_model = $this->add('Model_Account_Loan');
		if($_GET['filter']){
			if($_GET['account_no'])
				$account_no = $this->api->stickyGET('account_no');
		}else
			$account_no = -1;

		$account_model->addCondition('id',$account_no);

		$grid->addSno();
		// $grid->addColumn('member_name');
		// $grid->addColumn('loan_amount');
		// $grid->addColumn('monthly_interest');
		// $grid->addColumn('panality');
		// $grid->addColumn('other_charge');
		// $grid->addColumn('for_close_charge');
		// $grid->addColumn('time_over_charge');
		// $grid->addColumn('total_amount_deposited');
		// $grid->addColumn('for_close_amount');

		$grid->setModel($account_model,array('AccountNumber','member','Amount','CurrentInterest'));
		$grid->addPaginator(5);

		if($form->isSubmitted()){
			$grid->js()->reload(array('account_no'=>$form['account_no'],'filter'=>1))->execute();
		}	

	}

}
