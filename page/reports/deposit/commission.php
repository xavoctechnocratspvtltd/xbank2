<?php

class page_reports_deposit_commission extends Page {
	public $title="Commission Report";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$array1=array('All');
		$array2=explode(',', ACCOUNT_TYPES);
		$array=array_merge($array1,$array2);

		$form->addField('dropdown','account_type')->setValueList($array)->setEmptyText('Please Select');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		if($_GET['filter']){


		}

		$transaction=$this->add('Model_TransactionRow');
		$grid->setModel($transaction);
		$grid->addPaginator(50);

		// $grid->addColumn('expander','accounts');

		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}	

	}
}