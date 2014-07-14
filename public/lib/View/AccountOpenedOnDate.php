<?php


class View_AccountOpenedOnDate extends View{
	
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Account Openned Today','icon'=>'flag'));
		$container = $this->add('View');

		$this->js(true,$container->js()->hide());
		$heading->js('click',$container->js()->toggle());

		$from_date = $this->api->today;
		$to_date = $this->api->nextDate($this->api->today);
		if($_GET['from_date'])
			$from_date = $_GET['from_date'];
		if($_GET['to_date'])
			$to_date = $this->api->nextDate($_GET['to_date']);

		$form=$container->add('Form');
		$form->addField('DatePicker','from_date')->set($from_date);
		$form->addField('DatePicker','to_date')->set($to_date);
		$form->addSubmit('Get List');

		// === ACCOUNT VIEW
		$account_view = $container->add('View');

		$accounts_model = $this->add('Model_Account');
		$member_join = $accounts_model->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('PermanentAddress');
		$member_join->addField('PhoneNos');
		
		$accounts_model->addCondition('created_at','>=',$from_date);
		$accounts_model->addCondition('created_at','<',$to_date);

		$grid = $account_view->add('Grid');
		$grid->setModel($accounts_model,array('AccountNumber','member','scheme','Amount','FatherName','PermanentAddress','PhoneNos'));

		$grid->addPaginator(100);

		if($form->isSubmitted()){
			$container->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
				))->execute();
		}

	}

}