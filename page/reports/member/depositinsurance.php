<?php
class page_reports_member_depositinsurance extends Page {
	public $title="Deposit Member Insurance Report";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','type')->setValueList(array('RD'=>'RD','FD'=>'FD','MIS'=>'MIS',0=>'All'));
		$form->addSubmit('GET List');


		$grid=$this->add('Grid'); 

		$accounts_model=$this->add('Model_Account');
		$accounts_model->addCondition('Amount','<=',500);

		if($_GET['filter']){

			if($_GET['from_date'])
				$accounts_model->addCondition('LoanInsurranceDate','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$accounts_model->addCondition('LoanInsurranceDate','<=',$_GET['to_date']);
			if($_GET['type'])
				$accounts_model->addCondition('account_type',$_GET['type']);
		}
		// }else
			// $accounts_model->addCondition('id',-1);

		$grid->setModel($accounts_model->debug());

		$grid->addPaginator(50);


		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'type'=>$form['type'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	
	

	}
}