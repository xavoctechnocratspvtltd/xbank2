<?php
class Model_Employee extends Model_Table{
	public $table="xbank_employees";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->add('filestore/Field_Image','employee_image_photo_id');
		$this->add('filestore/Field_Image','employee_image_signature_id');

		$this->addField('name')->mandatory(true)->caption('Employee Name');
		$this->addField('emp_code')->caption('Emp. Code')->set($this->id);
		$this->addField('designation');
		$this->addField('department')->caption('Department/Location');
		$this->addField('DOB')->caption('Date of Birth')->type('date');
		$this->addField('experince')->caption('Total Experince');
		$this->addField('prev_company')->caption('Previous Company');
		$this->addField('prev_department')->caption('Previous Designation');
		$this->addField('prev_leaving_company_date')->type('date')->caption('Date of Leaving in  Previous Company ');
		$this->addField('leaving_resion');
		$this->addField('emergency_no');
		$this->addField('father_name');
		$this->addField('mother_name');
		$this->addField('marital_status');
		$this->addField('relation_with_nominee')->enum(array('Father','Mother','Wife','Husband','Son','Brother','Sister','Doughter'));
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
		$this->addField('opening_cl')->defaultValue(0);
		$this->addField('effective_cl_date')->type('date');
		$this->addHook('beforeSave',$this);
		$this->hasMany('EmployeeSalary','employee_id');



		$this->addExpression('ccl_availed')->set(function($m,$q){
			return $q->expr("IFNULL([0],0)",
			array(
				$m->refSQL('EmployeeSalary')->addCondition('salary_date','>',$q->getField('effective_cl_date'))->sum('CCL')
				)
			);
		});

		$this->addExpression('cl_availed')->set(function($m,$q){
			return $q->expr("IFNULL([0],0)",
			array(
					$m->refSQL('EmployeeSalary')->addCondition('salary_date','>',$q->getField('effective_cl_date'))->sum('CL')
				)
			);

		});

		$this->addExpression('cl_allowed')->set(function($m,$q){
			return $q->expr(
				"IFNULL([0],0) + period_diff(date_format(now(), '%Y%m'), date_format([1], '%Y%m')) + IFNULL([2],0) - IFNULL([3],0)",
				array(
					$m->getElement('opening_cl'),
					$m->getElement('effective_cl_date'),
					$m->getElement('ccl_availed'),
					$m->getElement('cl_availed')
					)
				);		
		})->caption('CL Allowed (as on actual date)');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function empSalary(){
		$emp = $this->ref('EmployeeSalary');
		if($emp->loaded()) return $emp;
		return false;
	}

	function beforeSave(){		
		if(strlen($this['contact_no']) !=10){
			throw $this->exception('Contact No must be 10 digits ','ValidityCheck')->setField('contact_no');
		}
	}
}