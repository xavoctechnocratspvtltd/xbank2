<?php

class View_Lister_EmployeeSalary extends CompleteLister{
	function init(){
		parent::init();
	}

	function defaultTemplate(){
		return array('view/emp_salary');
	}
}