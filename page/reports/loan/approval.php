<?php
class page_reports_loan_approval extends Page {

	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','account')->setModel('Active_Account_Loan','AccountNumber');
		$form->addSubmit('GET');

		$account_model = $this->add('Model_Account');
		$account_model->tryLoadBy('AccountNumber',$_GET['account']);

		$letter = $this->add('View',null, null, array('view/approval-letter'));
		if($_GET['account']){
			$letter->setModel($account_model);

			$member= $account_model->ref('member_id');

			$letter->template->trySet('branch',$account_model->ref('branch_id')->get('name'));
			// $letter->template->trySet('ref','Ref123');
			$letter->template->trySet('date',date('d/M/Y',strtotime($account_model['created_at'])));
			$letter->template->trySet('member_name',$member['name']);
			$letter->template->trySet('father_name',$member['FatherName']);
			$letter->template->trySet('address',$member['PermanentAddress']);
			$letter->template->trySet('loan_amount',$account_model['Amount']);
			$letter->template->trySet('interest_rate',$account_model->ref('scheme_id')->get('Interest'));
			$letter->template->trySet('premium_amount',$account_model->ref('Premium')->tryLoadAny()->get('Amount'));
			$letter->template->trySet('number_of_premium',$account_model->ref('Premium')->count());
			$letter->template->trySet('date_of_submission',(date('d',strtotime($account_model->ref('Premium')->tryLoadAny()->get('created_at')))));
		}

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			$this->js()->_selector('.atk-layout-column h2')->toggle(),
			);

		$letter->js('click',$js);

		if($form->isSubmitted()){
			$letter->js()->reload(array('account'=>$form['account']))->execute();
		}
				
	}

}