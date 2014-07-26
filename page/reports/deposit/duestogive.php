<?php

class page_reports_deposit_duestogive extends Page {
	public $title="Dues To Give";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('dropdown','account_type')->setValueList(array('all'=>'All','rd'=>'RD','fd'=>'FD','dds'=>'DDS','mis'=>'MIS'));
		$form->addField('DatePicker','from_date');
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
			$grid->js()->reload(array('account_type'=>$form['account_type'],'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'],'filter'=>1))->execute();
		}

	}
}