<?php

class Grid_EmployeeRecord extends Grid_AccountsBase{
	public $sno=1;
	public $sno_caption='s_no';
	function init(){
		parent::init();

		$salary_print = $this->addColumn('Button','print');
		if($_GET['print']){
			$this->js()->univ()->newWindow($this->api->url('employeesalaryprint',array('salary_id'=>$_GET['print'],'cut_page'=>0)))->execute();
		}

		$this->addSno();
		// $this->addPaginator($ipp=50);
	}
	
	function setModel($model,$field=array()){
		// $field=array('branch','emp_code','name','designation',
		// 			'contact_no','department','date_of_joining','emergency_no',
		// 			'father_name','mother_name','DOB','marital_status',
		// 			'last_qualification','email_id','permanent_address','present_address',
		// 			'pan_no','driving_licence_no','validity_of_driving_licence','bank_name',
		// 			'bank_account_no','experince','prev_company','prev_department',
		// 			'prev_leaving_company_date','leaving_resion','pf_joining_date','pf_no',
		// 			'pf_nominee','relation_with_nominee','pf_deduct','esi_no',
		// 			'esi_nominee','agreement_date','paymemt_mode','employee_status',
		// 			'basic_salary','other_allowance','society_contri','net_payable',
		// 			'net_salary','employee_image_photo','employee_image_signature','date_of_leaving',
		// 			'is_active');
		$m=parent::setModel($model,$field);
		$this->addTotals(array('pf_salary','pf_amount','ded','net_payable'));
		$this->addSno();
		// $this->addQuickSearch(array('name','emp_code','contact_no','father_name','pf_no','bank_name','bank_account_no'));
		// $c->grid->->move('edit','first')->now();
		// $order=$this->addOrder();
  //  		$order->move('emp_code','after','branch')->now();
  //  		$order->move('name','after','emp_code')->now();
  		// $$order->move($this->getElement('customer_email'),'first');

		return $m;
	}

	function add_sno(){
		$this->addColumn('sno',$this->sno_caption);
		$this->order->move($this->sno_caption,'first');
        return $this;
	}

    function addSno(){
        return $this->add_sno();
    }

}

