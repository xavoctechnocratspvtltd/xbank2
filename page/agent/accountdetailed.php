<?php
class page_agent_accountdetailed extends Page {
	public $title="Loan Account Detailed Report";
	function init(){
		parent::init();


		$form=$this->add('Form');
		$accounts_no_field=$form->addField('autocomplete/Basic','accounts_no');
		$accounts=$this->add('Model_Account');
		$accounts->addCondition('agent_id',$this->api->auth->model->id);
		$accounts_no_field->setModel($accounts);

		$form->addSubmit('GET List');


		// $grid=$this->add('Grid'); 

		// $accounts_model=$this->add('Model_Account_DDS');
		$accounts_model=$this->add('Model_Account');
		// $accounts_model->setOrder('id','desc');


		if($this->api->stickyGET('accounts_no')){
			$accounts_model->load($_GET['accounts_no']);
		}else{

		}

		$account_view = $this->add('View_AccountDetail',array('account'=>$accounts_model,'show_pan_adhaar'=>false));
		
		// $grid->setModel($accounts_model);

		// $grid->addPaginator(50);

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$account_view->js('click',$js);


		if($form->isSubmitted()){
			$account_view->js()->reload(array('accounts_no'=>$form['accounts_no']))->execute();

		}	
	

	}
}