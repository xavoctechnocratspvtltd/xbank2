<?php

class page_reports_pandl extends Page {
	public $title="Profit And Loss Sheet";

	function page_index(){

		$container = $this->add('View');

		$fy = $this->api->getFinancialYear('2014-01-01');

		if(!$_GET['from_date'])
			$from_date = $fy['start_date'];
		else{
			$from_date = $_GET['from_date'];
			$this->api->stickyGET('from_date');
		}

		if(!$_GET['to_date'])
			$to_date = $fy['end_date'];
		else{
			$to_date = $_GET['to_date'];
			$this->api->stickyGET('to_date');
		}

		$bs = $this->add('View_AccountSheet',array('from_date'=>$from_date,'to_date'=>$to_date,'pandl'=>true));

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('Go');

		if($form->isSubmitted()){
			$bs->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0
				))->execute();	
		}
	}

	function page_Details(){
		$this->api->stickyGET('_id');

		$this->add('View_Info')->set($_GET['_id']);
		
		$fy = $this->api->getFinancialYear('2014-01-01');

		if(!$_GET['from_date'])
			$from_date = $fy['start_date'];
		else{
			$from_date = $_GET['from_date'];
			$this->api->stickyGET('from_date');
		}

		if(!$_GET['to_date'])
			$to_date = $fy['end_date'];
		else{
			$to_date = $_GET['to_date'];
			$this->api->stickyGET('to_date');
		}

		$bs= $this->add('Model_BalanceSheet')->load($_GET['_id']);

		

	}
}