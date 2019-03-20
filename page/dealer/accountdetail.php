<?php

class page_dealer_accountdetail extends page_dealer_dashboard{
	
	function init(){
		parent::init();
		
		$tab = $this->add('Tabs');
		$tab->addTab('Account Detail');

		$form=$this->add('Form');
		$accounts_no_field=$form->addField('autocomplete/Basic','accounts_no');
		$accounts=$this->add('Model_Account');
		$accounts->addCondition('dealer_id',$this->app->auth->model->id);
		$accounts_no_field->setModel($accounts);

		$form->addSubmit('GET List');

		$accounts_model=$this->add('Model_Account');
		$accounts_model->addCondition('dealer_id',$this->app->auth->model->id);
		if($this->api->stickyGET('accounts_no')){
			$accounts_model->load($_GET['accounts_no']);
		}else{

		}

		$account_view = $this->add('View_AccountDetail',array('account'=>$accounts_model));

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