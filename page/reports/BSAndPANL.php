<?php

class page_reports_BSAndPANL extends Page {
	public $title="Account Sheet";

	function page_index(){

		$container = $this->add('View');
		$msg="BalanceSheet :: All";

		$fy = $this->api->getFinancialYear();

		if(!$_GET['from_date']){
			$from_date = $fy['start_date'];
		}
		else{
			$this->api->stickyGET('from_date');
			$from_date = $_GET['from_date'];
		}

		if(!$_GET['to_date']){
			$to_date = $fy['end_date'];
		}
		else{
			$this->api->stickyGET('to_date');
			$to_date = $_GET['to_date'];
		}

		if($this->api->auth->model['AccessLevel'] <=80)
			$for_branch = $this->api->current_branch;
		else
			$for_branch = false;

		switch ($_GET['book_type']) {
			case 'pandl':
				$pandl=true;
				$this->title= 'Profit And Loss Account';
				if($for_branch) $msg = $for_branch['name'].' :: Profit And Loss Account';
				$this->js(true)->_selector('H2')->html($msg);
				$book_type='pandl';
				break;
			
			default:
				$pandl=false;
				$this->title='Balance Sheet';
				$book_type='bs';
				if($for_branch) $msg = $for_branch['name'].' :: Balance Sheet';
				$this->js(true)->_selector('H2')->html($msg);
				break;
		}



		$bs = $this->add('View_AccountSheet',array('from_date'=>$from_date,'to_date'=>$to_date,'pandl'=>$pandl,'for_branch'=>$for_branch));

		$form = $this->add('Form')->addClass('noneprintalbe');
		$form->addField('DatePicker','from_date')->validateNotNull()->set($from_date);
		$form->addField('DatePicker','to_date')->validateNotNull()->set($to_date);
		$form->addField('Radio','book_type')->setValueList(array('bs'=>'Balance Sheet','pandl'=>'Profit And Loss','tb'=>'Trial balance'))->set($book_type);
		$form->addSubmit('Go');

		if($form->isSubmitted()){
			$this->js()->reload(array(
					'from_date'=>$form['from_date']?:0,
					'to_date'=>$form['to_date']?:0,
					'book_type'=>$form['book_type']
				))->execute();	
		}
	}

	// function page_pandlDetails(){
	// 	$this->api->stickyGET('_id'); //bs id
	// 	echo "okey";
	// }

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

		// DEtails based on bs
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

	function page_group2scheme(){
		$this->api->stickyGET('_id'); // bs id
		$bs= $this->add('Model_BalanceSheet')->load($_GET['_id']);

		$this->api->stickyGET('SchemeGroup');
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

		$this->add('View_BSPLChunks_Schemes',array('under_balance_sheet_id'=>$bs->id,'under_scheme_group'=>$_GET['SchemeGroup'],'from_date'=>$from_date,'to_date'=>$to_date,'branch'=>$for_branch));


	}

	function page_scheme2accounts(){
		$this->api->stickyGET('_id'); // bs id
		$bs= $this->add('Model_BalanceSheet')->load($_GET['_id']);
		
		$this->api->stickyGET('Scheme');
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

		$this->add('View_BSPLChunks_Accounts',array('under_balance_sheet_id'=>$bs->id,'under_scheme'=>$_GET['Scheme'],'from_date'=>$from_date,'to_date'=>$to_date,'branch'=>$for_branch));

	}

	function page_accounts2statement(){
		echo $_GET['AccountNumber'];
	}

	function page_Details_details2scheme(){
		$this->api->stickyGET('SchemeGroup');
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

		$schemes = $this->add('Model_Scheme');
		$schemes->addCondition('SchemeGroup',$_GET['SchemeGroup']);

		$result_array=array();
		foreach ($schemes as $s) {
			$op_bal = $s->getOpeningBalance($this->api->nextDate($to_date),$side='both',$forPandL=false,$branch=$for_branch);
			$result_array[] = array('Scheme'=>$s['name'],'Amount'=>$op_bal['Dr']-$op_bal['Cr']);
		}

		$grid = $this->add('Grid_BalanceSheet');
		$grid->setSource($result_array);

		$grid->addColumn('text,SchemeNameToAccounts','Scheme');
		$grid->addColumn('money','Amount');

		$grid->addTotals(array('Amount'));

	}

	function page_Details_details2scheme_scheme2account(){
		echo "level2";
		return;

		$this->api->stickyGET('Scheme');

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

		$schemes = $this->add('Model_Scheme');
		$schemes->addCondition('SchemeGroup',$_GET['SchemeGroup']);

		$result_array=array();
		foreach ($schemes as $s) {
			$op_bal = $s->getOpeningBalance($this->api->nextDate($to_date),$side='both',$forPandL=false,$branch=$for_branch);
			$result_array[] = array('Scheme'=>$s['name'],'Amount'=>$op_bal['Dr']-$op_bal['Cr']);
		}

		$grid = $this->add('Grid_BalanceSheet');
		$grid->setSource($result_array);

		$grid->addColumn('text,SchemeNameToAccounts','Scheme');
		$grid->addColumn('money','Amount');

		$grid->addTotals(array('Amount'));

	}
}