<?php
class Model_Employee extends Model_Table{
	public $table="xbank_employees";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addField('name')->mandatory(true)->caption('Employee');
		$this->addField('emp_code')->caption('Emp. Code');
		$this->addField('designation');
		$this->addField('department')->caption('Department/Location');
		$this->addField('DOB')->caption('Date of Birth')->type('date');
		$this->addField('experince')->caption('Total Experince');
		$this->addField('prev_company')->caption('Previous Company');
		$this->addField('prev_department')->caption('Previous Designation');
		$this->addField('prev_leaving_company_date')->type('date')->caption('Date of Leaving in  Previous Company ');
		$this->addField('leaving_resion');
		$this->addField('father_name');
		$this->addField('mother_name');
		$this->addField('marital_status');
		$this->addField('relation_with_nominee');
		$this->addField('last_qualification');
		$this->addField('contact_no');
		$this->addField('email_id');
		$this->addField('permanent_address');
		$this->addField('present_address');
		$this->addField('date_of_joining')->type('date');
		$this->addField('date_of_leaving')->type('date');
		$this->addField('pf_no');
		$this->addField('pf_nominee');
		$this->addField('esi_no');
		$this->addField('esi_nominee');
		$this->addField('pan_no');
		$this->addField('driving_licence_no');
		$this->addField('validity_of_driving_licence');
		$this->addField('pf_joining_date');
		$this->addField('agreement_date');
		$this->addField('bank_name');
		$this->addField('bank_account_no');
		$this->addField('paymemt_mode');
		$this->addField('pf_deduct')->enum(array('YES','NO'))->defaultValue('YES');
		$this->addField('employee_status');
		$this->addField('basic_salary');
		$this->addField('other_allowance');
		$this->addField('society_contri');
		$this->addField('net_payable');
		$this->addField('net_salary');
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->hasMany('EmployeeSalary','employee_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function empSalary(){
		$emp = $this->ref('EmployeeSalary');
		if($emp->loaded()) return $emp;
		return false;
	}
}