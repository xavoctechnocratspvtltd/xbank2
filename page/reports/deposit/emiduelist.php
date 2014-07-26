<?php

class page_reports_deposit_emiduelist extends Page {
	public $title="Deposit EMI Due List";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','time_collaps'=>'Time Collaps'))->setEmptyText('Please Select');

		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		if($_GET['filter']){


		}

		$account=$this->add('Model_Account');
		$grid->setModel($account);
		$grid->addPaginator(50);

		$grid->addColumn('expander','accounts');

		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'],'filter'=>1))->execute();
		}	
	}
}