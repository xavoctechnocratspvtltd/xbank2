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

		$for_branch= (!$this->api->current_staff->isSuper()?$this->api->current_staff->branch():false);
		$bs = $this->add('View_AccountSheet',array('from_date'=>$from_date,'to_date'=>$to_date,'pandl'=>true,'for_branch'=>$for_branch));

		$form = $this->add('Form')->addClass('noneprintalbe');
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
		$this->api->stickyGET('_id'); // bs id
		
		$fy = $this->api->getFinancialYear();

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

		if($this->api->auth->model['AccessLevel'] <=80)
			$for_branch = $this->api->current_branch;
		else
			$for_branch = false;

		$bs= $this->add('Model_BalanceSheet')->load($_GET['_id']);

		// Details based on bs
		if($bs['show_sub']=='SchemeGroup'){
			$this->add('View_BSPLChunks_SchemeGroup',array('under_balance_sheet_id'=>$bs->id,'from_date'=>$from_date,'to_date'=>$to_date,'branch'=>$for_branch));
		}elseif($bs['show_sub']=='Accounts'){
			$this->add('View_BSPLChunks_Accounts',array('under_balance_sheet_id'=>$bs->id,'from_date'=>$from_date,'to_date'=>$to_date,'branch'=>$for_branch));
		}elseif($bs['show_sub']=='PAndLGroup'){
			$this->add('View_BSPLChunks_PAndLGroup',array('under_balance_sheet_id'=>$bs->id,'from_date'=>$from_date,'to_date'=>$to_date,'branch'=>$for_branch));
		}else{
			$this->add('View_Error')->set('Not Implemented yet');
		}

		

	}
}