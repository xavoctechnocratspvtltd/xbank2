<?php
class page_reports_loan_noc extends Page {

	function init(){
		parent::init();

		$form = $this->add('Form');
		$loan = $this->add('Model_Account_Loan');

		if($this->api->current_branch->id != 1)
			$loan->addCondition('branch_id',$this->api->current_branch->id);

		$loan->addCondition('AccountNumber','not like','%DFL%');
		$loan->addCondition('AccountNumber','not like','%Default%');
		$loan->addCondition('ActiveStatus',false);
		$form->addField('autocomplete/Basic','account')->setModel($loan);
		$form->addSubmit('GET');

		
		$account_model = $this->add('Model_Account_Loan')->addCondition('ActiveStatus',false)->addCondition('DefaultAC',false);

		$letter = $this->add('View',null, null, array('view/noc-letter'));
		if($_GET['account']){
			$account_model->tryLoad($_GET['account']);
			$letter->setModel($account_model);

			$member= $account_model->ref('member_id');
			$letter->template->trySet('member_name',$member['name']);
			$letter->template->trySet('father_name',$member['FatherName']);
			$letter->template->trySet('account_number',$account_model['AccountNumber']);

			$letter->template->trySet('vehicle_no',$account_model->getVehicalNo());
			$letter->template->trySet('chassis_no',$account_model->getChassisNo());
			$letter->template->trySet('engine_no',$account_model->getEngineNo());

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