<?php

class page_reports_deposit_emireceivedlist extends Page {
	public $title="Deposit Premium Received List";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','account_type')->setValueList(array('all'=>'All','rd'=>'RD','dds'=>'DDS'));

		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		if($_GET['filter']){


		}

		$transaction=$this->add('Model_TransactionRow');
		$grid->setModel($transaction);
		$grid->addPaginator(50);


		if($form->isSubmitted()){
			$grid->js()->reload(array('account_type'=>$form['account_type'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}
	}
}