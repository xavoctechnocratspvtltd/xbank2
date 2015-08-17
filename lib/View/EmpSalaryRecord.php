<?php

class View_EmpSalaryRecord extends View{
	function init(){
		parent::init();

		$this->api->stickyGET('salary_id');
		$emp=$this->add('Model_EmployeeSalary');
		$emp->load($_GET['salary_id']);

		
		$this->template->set('emp_name',$emp->ref('employee_id')->get('name'));
		$this->template->set('location',$emp->ref('employee_id')->get('branch'));
		$this->template->set('emp_code',$emp['employee_id']);
		$this->template->set('dob',$emp->ref('employee_id')->get('DOB'));
		$this->template->set('month',$emp['month']);
		$this->template->set('doj',$emp->ref('employee_id')->get('date_of_joining'));
		$this->template->set('designation',$emp->ref('employee_id')->get('designation'));
		$this->template->set('pro_fund_no',$emp->ref('employee_id')->get('pf_no'));
		$this->template->set('pan_no',$emp->ref('employee_id')->get('pan_no'));
		$this->template->set('basic_salary',$emp->ref('employee_id')->get('basic_salary'));
		$this->template->set('other_allowance',$emp->ref('employee_id')->get('other_allownace'));
		$this->template->set('net_salary',$emp['net_payable']);
		$this->template->set('department',$emp->ref('employee_id')->get('department'));
		
		$total=$emp->ref('employee_id')->get('basic_salary')+$emp->ref('employee_id')->get('other_allownace');
		$this->template->set('total',$total);

		$total_payable_amount=$emp['salary']+$emp['allow_paid'];
		$this->template->set('total_payable_amount',$total_payable_amount);

		$this->template->set('pay_salary',$emp['salary']);
		$this->template->set('pay_other_allw',$emp['allow_paid']);
		$this->template->set('working_day',$emp['total_days']);
		$this->template->set('p_day',$emp['paid_days']);
		
		$this->template->set('provided_fund',$emp['pf_amount']);
		$this->template->set('other_deduction',$emp['ded']);

		$total_deductoin_amount=$emp['pf_amount']+$emp['ded'];
		$this->template->set('total_deduction',$total_deductoin_amount);

		$this->template->set('cl',$emp['CL']);
		$this->template->set('ccl',$emp['CCL']);
		$this->template->set('lwp',$emp['LWP']);
		$this->template->set('absent',$emp['ABSENT']);
		$this->template->set('weekly_off',$emp['weekly_off']);

		$total_leave_count=$emp['CL']+$emp['CCL']+$emp['LWP']+$emp['ABSENT']+$emp['weekly_off'];
		$this->template->set('total_leave',$total_leave_count);

		
	}
	function setModel($model){
		parent::setModel($model);
	}
	function formatRow(){
		parent::formatRow();
	}

	function defaultTemplate(){
		return array('view/salaryprint');
	}
}