<?php


class View_AccountOpenedOnDate extends View{
	public $from_date;
	public $to_date;
	
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Account Openned Today','icon'=>'flag'));
		$container = $this->add('View');


		// === ACCOUNT VIEW
		$account_view = $container->add('View');

		$accounts_model = $this->add('Model_Account');
		$member_join = $accounts_model->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PhoneNos');
		
		$accounts_model->addCondition('created_at','>=',$this->from_date);
		$accounts_model->addCondition('created_at','<',$this->to_date);

		$grid = $account_view->add('Grid');
		$grid->setModel($accounts_model,array('AccountNumber','member','scheme','Amount','FatherName','PermanentAddress','PhoneNos'));

		$grid->addPaginator(100);

		

	}

}