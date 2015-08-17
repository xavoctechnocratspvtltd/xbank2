<?php
class Model_EmployeeSalary extends Model_Table{
	public $table='employee_salary_record';
	function init(){
		parent::init();
		$month=array( 'Jan'=>"Jan",'Feb'=>"Feb",'March'=>"March",'April'=>"April",
					'May'=>"May",'Jun'=>"Jun",'July'=>"July",'Aug'=>"Aug",'Sep'=>"Sep",
					'Oct'=>"Oct",'Nov'=>"Nov",'Dec'=>"Dec");

		$date=$this->api->today;
		$y=date('Y',strtotime($date));	
		for ($i=$y; $i >=1970 ; $i--) { 
			$years[]=$i;
		}

		$this->hasOne('Branch','branch_id');
		$this->hasOne('Employee','employee_id');
		$this->addField('month')->setValueList($month);
		$this->addField('year')->setValueList($years);
		$this->addField('CL');
		$this->addField('CCL');
		$this->addField('LWP');
		$this->addField('ABSENT');
		$this->addField('weekly_off');
		$this->addField('total_days')->defaultValue(0);
		$this->addField('paid_days')->defaultValue(0);
		$this->addField('leave')->defaultValue(0);
		$this->addField('salary')->defaultValue(0);
		$this->addField('pf_salary')->defaultValue(0);
		$this->addField('ded')->defaultValue(0);
		$this->addField('pf_amount')->defaultValue(0);
		$this->addField('allow_paid')->defaultValue(0);
		$this->addField('other_allowance')->defaultValue(0);
		$this->addField('net_payable')->defaultValue(0);
		$this->addField('narration')->defaultValue(0);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}