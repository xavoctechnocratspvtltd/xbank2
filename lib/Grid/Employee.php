<?php

class Grid_Employee extends Grid_AccountsBase{
	function init(){
		parent::init();

		$salary_print = $this->addColumn('Button','print');
		if($_GET['print']){
			$this->js()->univ()->newWindow($this->api->url('employeesalaryprint',array('salary_id'=>$_GET['print'],'cut_page'=>0)))->execute();
		}

		$this->addSno();
		// $this->addPaginator($ipp=50);
	}
	
	function setModel($model){
		$m=parent::setModel($model);

		$this->addTotals(array('pf_salary','ded','net_payable'));
		return $m;
	}
}