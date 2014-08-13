<?php
class page_reports_member_loaninsurance extends Page {
	public $title="Loan Member Insurance Report";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','type')->setValueList(array('vl'=>'VL','pl'=>'PL','cc'=>'CC','all'=>'All'));
		$form->addSubmit('GET List');


		$grid=$this->add('Grid_AccountsBase'); 

		$accounts_model=$this->add('Model_Account_Loan');

		if($_GET['filter']){

			//TODO

		}

		$grid->setModel($accounts_model);

		$grid->addPaginator(50);
		$grid->addSno();


		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'type'=>$form['type'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	
	

	}
}