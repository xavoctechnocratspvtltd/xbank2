<?php

class page_reports_deposit_tdsquaterly extends Page {
	public $title="TDS Quaterly Reports";
	function page_index(){
		// parent::init();

		$form=$this->add('Form');
		$dealer=$form->addField('DropDown','qtr')->setValueList(array('1'=>'1 Quarter','2'=>'2 Quarter','3'=>'3 Quarter','4'=>'4 Quarter'));

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('TDS '.$_GET['qty'].'Reports'); 

		$account_model = $this->add('Model_Account');
		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('name_and_address');
		$grid->addColumn('pan_no');
		$grid->addColumn('month');
		$grid->addColumn('tds_percent');
		$grid->addColumn('total_comm');
		$grid->addColumn('tds_amt');
		$grid->addColumn('date_of_tds');
		$grid->addColumn('ch_no');
		$grid->addColumn('net_amount');

		$grid->setModel();

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
