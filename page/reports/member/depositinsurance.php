<?php
class page_reports_member_depositinsurance extends Page {
	public $title="Deposit Member Insurance Report";
	
	function init(){
		parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','type')->setValueList(array('RD'=>'RD','FD'=>'FD','MIS'=>'MIS',0=>'All'));
		$form->addSubmit('GET List');


		$grid=$this->add('Grid'); 
		$grid->add('H3',null,'grid_buttons')->set('Deposit Insurance List As On '. date('d-M-Y',strtotime($till_date))); 

		$accounts_model=$this->add('Model_Account');
		$accounts_model->addCondition('Amount','<=',500);

		if($_GET['filter']){

			if($_GET['from_date'])
				$accounts_model->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$accounts_model->addCondition('created_at','<=',$_GET['to_date']);
			if($_GET['type'])
				$accounts_model->addCondition('account_type',$_GET['type']);
		}
		else
			$accounts_model->addCondition('id',-1);

		$grid->setModel($accounts_model);

		$grid->addPaginator(50);

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);

		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'type'=>$form['type'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	
	

	}
}