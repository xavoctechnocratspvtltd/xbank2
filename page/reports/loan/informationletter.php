<?php
class page_reports_loan_informationletter extends Page {

	function init(){
		parent::init();

		$form = $this->add('Form');
		$loan = $this->add('Model_Account_Loan');

		if($this->api->current_branch->id != 1)
			$loan->addCondition('branch_id',$this->api->current_branch->id);

		$loan->addCondition('AccountNumber','not like','%DFL%');
		$loan->addCondition('AccountNumber','not like','%Default%');
		$form->addField('autocomplete/Basic','account')->setModel($loan);
		$form->addSubmit('GET');

		
		$account_model = $this->add('Model_Account_Loan')->addCondition('DefaultAC',false);

		$letter = $this->add('View',null, null, array('view/information-letter'));
		if($_GET['account']){
			$account_model->load($_GET['account']);
			
			$letter->setModel($account_model);


			$member= $account_model->ref('member_id');

			$letter->template->trySet('member_name',$member['name']);
			$letter->template->trySet('father_name',$member['FatherName']);
			$letter->template->trySet('address',$member['PermanentAddress']);
			$letter->template->trySet('account_no',$account_model['AccountNumber']);
			$letter->template->trySet('total_amount',$account_model['Amount']);
			$letter->template->trySet('total_installment',$account_model->ref('Premium')->count()->getOne());
			$letter->template->trySet('date',date('d-m-Y',strtotime($this->api->today)));

		}

		$js=array(
			$this->js()->_selector('.banner')->toggle(),
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			$this->js()->_selector('.atk-layout-column h2')->toggle(),
			);

		$letter->js('click',$js);

		if($form->isSubmitted()){
			if(!$form['account'])
				$form->error('account','Select One Account');
			$letter->js()->reload(array('account'=>$form['account']))->execute();
		}
				
	}

}