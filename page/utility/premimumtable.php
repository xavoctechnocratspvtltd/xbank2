<?php
class Page_utility_premimumtable extends Page{
	public $title = 'Premium Table';
	function init(){
		parent::init();
		$form=$this->add('Form');
		$account_field = $form->addField('autocomplete/Basic','account')->validateNotNull();
		
		$premimum_model=$this->add('Model_Account');

		$account_field->setModel($premimum_model);
		
		$form->addSubmit('Get Details');
		$grid = $this->add('Grid');
		$m = $this->add('Model_Premium');

		if($this->api->stickyGET('account_id')){
			$m->addCondition('account_id',$_GET['account_id']);
		}
		$grid->setModel($m);
		$grid->addPaginator(50);
		$grid->addFormatter('Paid','grid/inline');
		$grid->addFormatter('PaidOn','grid/inline');
		$grid->addFormatter('AgentCommissionSend','grid/inline');
		$grid->addFormatter('AgentCollectionChargesSend','grid/inline');

		if($form->isSubmitted()){
			$grid->js()->reload(array('account_id'=>$form['account']))->execute();
		}
	}
}