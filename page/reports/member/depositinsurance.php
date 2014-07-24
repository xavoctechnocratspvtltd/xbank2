<?php
class page_reports_member_depositinsurance extends Page {
	public $title="Deposit Member Insurance Report";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','type')->setValueList(array('rd'=>'RD','fd'=>'FD','mis'=>'MIS','all'=>'All'));
		$form->addSubmit('GET List');


		$grid=$this->add('Grid'); 

		$accounts_model=$this->add('Model_Account');

		if($_GET['filter']){

			//TODO

		}

		$grid->setModel($accounts_model);

		$grid->addPaginator(50);


		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'type'=>$form['type'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	
	

	}
}