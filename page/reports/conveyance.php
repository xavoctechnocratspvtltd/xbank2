<?php

class page_reports_conveyance extends Page {

	public $title="Conveyance Book";

	function init(){
		parent::init();

		$this->add('Controller_Acl');
		$staff =  $this->add('Model_Employee');
		$form=$this->add('Form')->addClass('noneprintalbe');
		$staff_field = $form->addField('autocomplete/Basic','staff');
		$staff_field->setModel($staff);

		$sv_model = $this->add('Model_Account_SavingAndCurrent');
		$staff_sb_field = $form->addField('autocomplete/Basic','staff_sb');
		$staff_sb_field->setModel($sv_model);


		$form->addField('DatePicker','from_date')->set($_GET['from_date']);
		$form->addField('DatePicker','to_date')->set($_GET['to_date']);
		$form->addSubmit('Get Statement');
		$v = $this->add('View')->addStyle('width','100%');

		$tr_row = $this->add('Model_TransactionRow');
		$tr_row->addCondition('transaction_type',TRA_CONVEYANCE_CAHRGES);
		$tr_row->addCondition('amountCr','>',0);

		if($_GET['filter']){
			$this->app->stickyGET('filter');
			if($staff_id = $this->app->stickyGET('staff_id'))
				$tr_row->addCondition('reference_id',$staff_id);
			if($_GET['from_date'])
				$tr_row->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$tr_row->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
		}else{
				$tr_row->addCondition('id',-1);
		}


		if($form->isSubmitted()){
			if(!$form['staff_sb'] && !$form['staff'])  $form->displayError('staff_sb','Either Staff OR Staff SB must required');
						
			$v->js()->reload(
					array(
						'staff_id'=>($form['staff_sb']?$form['staff_sb']:$form['staff']),
						'from_date'=>($form['from_date'])?:0,
						'to_date'=>($form['to_date'])?:0,
						'filter'=>1,
						)
					)->execute();
		}

		$grid = $v->add('Grid_AccountStatement');
		$grid->setModel($tr_row,['account','scheme','amountCr','created_at','voucher_no','Narration']);
		$grid->addPaginator(50);
		$grid->addTotals(['amountCr','amountDr']);

	}
}