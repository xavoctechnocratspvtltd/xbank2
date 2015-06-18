<?php

class page_reports_general_memberdepositeandloan extends Page {
	public $title="Member Deposite and Loan Repots";
	function page_index(){
		// parent::init();

		$as_on_date = $this->api->today;
		if($_GET['as_on_date'])
			$as_on_date = $_GET['as_on_date'];

		$form=$this->add('Form');
		$form->addField('DatePicker','as_on_date');
		$account_type=$form->addField('DropDown','status')->setValueList(array(1=>'Active',0=>'Inactive'));
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_MemberDepositeAndLoan',array('as_on_date'=>$as_on_date));
		
		$grid->add('H3',null,'grid_buttons')->set('Member Deposite and Loan Report As On '. date('d-M-Y',strtotime($as_on_date)));

		$member_model = $this->add('Model_Member');
		$member_model->addExpression('sm_no')->set(function($m,$q){
			return $m->refSQL('Account')->addCondition('SchemeType','Default')->addCondition('scheme_name','Share Capital')->fieldQuery('AccountNumber');
		});

		$member_model->addExpression('share_account_amount')->set(function($m,$q){
			return $m->refSQL('Account')->addCondition('SchemeType','Default')->addCondition('scheme_name','Share Capital')->fieldQuery('Amount');
		});

		$member_model->addExpression('add_fees')->set(function($m,$q)use($member_model){
			$transaction_type_model = $this->add('Model_TransactionType');
			$transaction_type_model->addCondition('name','NewMemberRegistrationAmount');
			return $m->add('Model_Transaction')->addCondition('transaction_type_id',$transaction_type_model->fieldQuery('id'))->addCondition('reference_id',$member_model->getElement('id'))->sum('cr_sum');
		});

		if($form->isSubmitted()){
			$grid->js()->reload(array('as_on_date'=>$form['as_on_date']?:0,'status'=>$form['status'],'filter'=>1))->execute();
		}

		if($_GET['filter']){
			if($_GET['as_on_date'])
				$member_model->addCondition('created_at','<=',$as_on_date);
			
			$member_model->addCondition('is_active',$_GET['status']);
		}

		$grid->setModel($member_model,
								array('member_name',
										'FatherName',
										'sm_no',
										'share_account_amount',
										'add_fees',
									)
						);

	}

}
