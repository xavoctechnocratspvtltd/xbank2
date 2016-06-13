<?php

class View_EmpSalaryRecord extends View{
	function init(){
		parent::init();

		$this->api->stickyGET('salary_id');
		$emp=$this->add('Model_EmployeeSalary');
		$emp->load($_GET['salary_id']);

		$this->template->set('emp_name',$emp->ref('employee_id')->get('name'));
		if($emp->ref('employee_id')->get('branch')=='Default'){
			// throw new Exception("Error Processing Request", 1);
			$this->template->set('location','Head Office Udaipur');
		}else{
			$this->template->set('location',$emp->ref('employee_id')->get('branch'));
		}
		$this->template->set('emp_code',$emp['employee_code']);
		$this->template->set('dob',date('d-m-Y',strtotime($emp->ref('employee_id')->get('DOB'))));
		$this->template->set('month',date($emp['month'].' - ' .$emp['year']));
		$this->template->set('doj',date('d-m-Y',strtotime($emp->ref('employee_id')->get('date_of_joining'))));
		$this->template->set('designation',$emp->ref('employee_id')->get('designation'));
		$this->template->set('pro_fund_no',$emp->ref('employee_id')->get('pf_no'));
		$this->template->set('pan_no',$emp->ref('employee_id')->get('pan_no'));
		$this->template->set('basic_salary',$emp->ref('employee_id')->get('basic_salary'));
		$this->template->set('other_allowance',$emp->ref('employee_id')->get('other_allowance'));
		$this->template->set('incentive',$emp['incentive']);
		$this->template->set('net_salary',$emp['net_payable']);
		$this->template->set('department',$emp->ref('employee_id')->get('department'));
		
		$total=round($emp->ref('employee_id')->get('basic_salary') + $emp->ref('employee_id')->get('other_allowance')+$emp['incentive']);
		$this->template->set('total',$total);

		$total_payable_amount=round($emp['salary']+$emp['allow_paid']+$emp['incentive']);
		$this->template->set('total_payable_amount',$total_payable_amount);

		$this->template->set('pay_salary',round($emp['salary']));
		$this->template->set('pay_other_allw',round($emp['allow_paid']));
		$this->template->set('working_day',$emp['total_days']);
		$this->template->set('p_day',$emp['paid_days']);
		
		$this->template->set('provided_fund',round($emp['pf_amount']));
		$this->template->set('other_deduction',round($emp['ded']));

		$total_deductoin_amount=round($emp['pf_amount']+$emp['ded']);
		$this->template->set('total_deduction',$total_deductoin_amount);
		$this->template->set('avaied_month',date('M',strtotime($emp['month'])));
		$this->template->set('avaied_year',date('Y',strtotime($emp['year'])));
		$this->template->set('leave_month',date('M',strtotime($emp['month'])));
		$this->template->set('leave_year',date('Y',strtotime($emp['year'])));

		$this->template->set('total_cl',$emp->ref('employee_id')->get('cl_allowed'));
		$this->template->set('month_cl',$emp['CL']);
		
		$balance_cl=$emp->ref('employee_id')->get('cl_allowed') - $emp['CL'];

		$this->template->set('balance_cl',$balance_cl);
		$this->template->set('cl',$emp['CL']);
		$this->template->set('ccl',$emp['CCL']);
		$this->template->set('lwp',$emp['LWP']);
		$this->template->set('absent',$emp['ABSENT']);
		$this->template->set('weekly_off',$emp['monthly_off']);

		$total_leave_count=$emp['CL']+$emp['CCL']+$emp['LWP']+$emp['ABSENT']+$emp['weekly_off'];
		$this->template->set('total_leave',$total_leave_count);
		$this->template->set('amount_in_words',$emp->convert_number_to_words($emp['net_payable']));
		$this->template->set('date_of_generate',date('d-m-Y',strtotime($this->api->today)));
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