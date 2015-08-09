<?php

class Grid_Employee extends Grid{
	function init(){
		parent::init();

		$salary_print = $this->addColumn('Button','print');
		if($_GET['print']){
			$this->js()->univ()->newWindow($this->api->url('employeesalaryprint',array('salary_id'=>$_GET['print'],'cut_page'=>0)))->execute();
		}

		// $this->addPaginator($ipp=50);
	}
	
	function setModel($model){
		$m=parent::setModel($model);
		return $m;
	}
}