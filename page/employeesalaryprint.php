<?php
class page_employeesalaryprint extends Page{
	function init(){
		parent::init();
		
		$this->api->stickyGET('salary_id');
		$emp=$this->add('Model_EmployeeSalary')->load($_GET['salary_id']);
		$this->add('View_EmpSalaryRecord')->set($emp);
	}
}