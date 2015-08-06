<?php

class page_employee extends Page{
	public $title ='Employee Management';
	function page_index(){
		// parent::init();

		$tab=$this->add('Tabs');
		$tab->addTabURL('./addEmployee','Add Employees');
		$tab->addTabURL('./manageSalary','Salary Structure');
		$tab->addTabURL('./salaryRecord','Salary Managment');

	}

	function page_addEmployee(){
		$crud=$this->add('CRUD');
		$crud->setModel('Employee');
		$crud->addRef('EmployeeSalary');
	}


	function page_manageSalary(){
		$this->api->stickyGET('branch');
		$this->api->stickyGET('month');
		$this->api->stickyGET('year');
		$this->api->stickyGET('working_day');
	
		$month=array( 'Jan'=>"Jan",'Feb'=>"Feb",'March'=>"March",'April'=>"April",
					'May'=>"May",'Jun'=>"Jun",'July'=>"July",'Aug'=>"Aug",'Sep'=>"Sep",
					'Oct'=>"Oct",'Nov'=>"Nov",'Dec'=>"Dec");
		
		$date=$this->api->today;
		$y=date('Y',strtotime($date));	
		for ($i=$y; $i >=1970 ; $i--) { 
			$years[]=$i;
		}

		$emp_model=$this->add('Model_Employee');
		$emp_salary_j=$emp_model->LeftJoin('employee_salary_record.id');
		$emp_salary_j->addField('employee_id');
		$emp_salary_j->addField('month');
		$emp_salary_j->addField('year');
		$emp_salary_j->addField('paid_days');
		$emp_salary_j->addField('total_days');
		$emp_salary_j->addField('leave');
		$emp_salary_j->addField('salary');
		// $emp_salary_j->addField('pf_salary');
		$emp_salary_j->addField('ded');
		$emp_salary_j->addField('pf_amount');
		// $emp_salary_j->addField('other_allownace');
		$emp_salary_j->addField('allow_paid');
		$emp_salary_j->addField('narration');
		
		// $emp_model->addCondition('month',$_GET['month']);
		// $emp_model->addCondition('month',$_GET['year']);
		$record_form=$this->add('Form',null,null,array('form/empty'));
		// $record_form->addStyle(array('border'=>'2px solid black'));
		$col1=$record_form->add('Columns');
		$col1->addColumn(3)->addField('Dropdown','branch')->setEmptyText('Please Select Branch')->validateNotNull(true)->setModel('Branch');
		$col1->addColumn(3)->addField('Dropdown','month')->setValueList($month)->setEmptyText('Please Select Month')->validateNotNull(true);
		$col1->addColumn(3)->addField('Dropdown','year')->setValueList($years)->setEmptyText('Please Select Branch')->validateNotNull(true);
		$col1->addColumn(3)->addField('line','working_day');
		$record_form->add('View')->set('')->addClass('well');

		$lable_c=$record_form->add('Columns');
		$lable_c->addColumn(1)->add('H4')->set('Name & B. Salary');
		$lable_c->addColumn(1)->add('H4')->set('T. Days');
		$lable_c->addColumn(1)->add('H4')->set('Paid Days');
		$lable_c->addColumn(1)->add('H4')->set('Leave');
		$lable_c->addColumn(1)->add('H4')->set('Salary');
		$lable_c->addColumn(1)->add('H4')->set('PF Salary');
		$lable_c->addColumn(1)->add('H4')->set('DED.');
		$lable_c->addColumn(1)->add('H4')->set('PF AMT');
		$lable_c->addColumn(1)->add('H4')->set('Other ALW.');
		$lable_c->addColumn(1)->add('H4')->set('ALW. Paid');
		$lable_c->addColumn(1)->add('H4')->set('Net Payable');
		$lable_c->addColumn(1)->add('H4')->set('Narrtion');
		foreach ($emp_model as  $junk) {
			$col= $record_form->add('Columns');
			$cl=$col->addColumn(1)->addClass('atk-col-1');
			$cl->add('View')->setHtml($emp_model['name']."&nbsp<br/>[ ".$emp_model['basic_salary'].' ]');
			$cl->addField('hidden','name_'.$emp_model['id'])->set($emp_model['name']);
			$basic_salary = $cl->addField('hidden','basic_salary_'.$emp_model['id'])->set($emp_model['basic_salary']);
			
			$t_c=$col->addColumn(1);
			$t_c->add('View')->set($emp_model['total_days']);
			$td = $t_c->addField('hidden','total_days_'.$emp_model['id'])->set($emp_model['total_days']);
			
			$pd = $col->addColumn(1)->addField('line','paid_days_'.$emp_model['id'])->set($emp_model['paid_days']);
			$col->addColumn(1)->addField('line','leave_'.$emp_model['id'])->set($emp_model['leave']);
			
			$new_salary_amount=($emp_model['basic_salary'] / $emp_model['total_days'] * $emp_model['paid_days']); 
			if($new_salary_amount==0)
				$new_salary_amount=1;
			
			$s_c=$salary=$col->addColumn(1);
			$salary_f = $s_c->addField('hidden','salary_'.$emp_model['id'])->set($new_salary_amount);
			$s_c->add('View')->setHtml($emp_model['salary'].'&nbsp');
			
			$p_c=$col->addColumn(1);
			$pf=$p_c->addField('hidden','pf_salary_'.$emp_model['id'])->set($emp_model['salary']);
			$p_c->add('View')->setHtml($emp_model['salary'].'&nbsp');
			
			$new_pf_amount =  round(($emp_model['pf_salary'] / 100) * 12,2);

			$ded=$col->addColumn(1)->addField('line','ded_'.$emp_model['id'])->set($emp_model['ded']);
			
			$pf_amount=$col->addColumn(1)->addField('line','pf_amount_'.$emp_model['id'])->set($new_pf_amount);
			
			$o_c=$col->addColumn(1);
			$o_c->addField('hidden','other_allowance_'.$emp_model['id'])->set($emp_model['other_allownace']);
			$o_c->add('View')->setHtml($emp_model['other_allownace'].'&nbsp');
			
			$ap=$col->addColumn(1)->addField('line','allow_paid_'.$emp_model['id'])->set($emp_model['allow_paid']);
			
			$new_nt_amount= ($emp_model['salary'] + $emp_model['allow_paid'] - $emp_model['ded']-$emp_model['pf_amount']);			

			$n_c=$col->addColumn(1);
			$nt=$n_c->addField('line','net_payable_'.$emp_model['id'])->set($new_nt_amount);
			// $n_c->add('View')->setHtml($emp_model['net_payable'].'&nbsp');
			
			$col->addColumn(1)->addField('line','narration_'.$emp_model['id'])->set($emp_model['narration']);
			
			$ded->js( 'change')->univ()->netpayable($nt,$salary,$ap,$ded,$pf_amount);
			$ap->js( 'change')->univ()->netpayable($nt,$salary,$ap,$pf_amount,$ded);
			$pf_amount->js( 'change')->univ()->netpayable($nt,$salary,$ap,$pf_amount,$ded);
			$pd->js( 'change')->univ()->salary($salary_f,$basic_salary,$td,$pd);
		}

		$record_form->addSubmit('Go');

		if($record_form->isSubmitted()){
			foreach ($emp_model as  $junk) {
				$salary = $this->add('Model_EmployeeSalary');
				$salary->addCondition('employee_id', $emp_model->id);
				$salary->addCondition('branch_id', $record_form['branch']);
				$salary->addCondition('month', $record_form['month']);
				$salary->addCondition('year', $record_form['year']);
				
				$salary->tryLoadAny();

				$salary['total_days']=$record_form['total_days_'.$emp_model['id']];
				$salary['paid_days']=$record_form['paid_days_'.$emp_model['id']]?$salary['paid_days']:$record_form['working_day'];
				$salary['leave']=$record_form['leave_'.$emp_model['id']];
				$salary['salary']=$record_form['salary_'.$emp_model['id']];
				$salary['pf_salary']=$record_form['pf_salary_'.$emp_model['id']];
				$salary['ded']=$record_form['ded_'.$emp_model['id']];
				$salary['pf_amount']=$record_form['pf_amount_'.$emp_model['id']];
				$salary['allow_paid']=$record_form['allow_paid_'.$emp_model['id']];
				$salary['other_allownace']=$record_form['other_allowance_'.$emp_model['id']];
				$salary['net_payable']=$record_form['net_payable_'.$emp_model['id']];
				$salary['narration']=$record_form['narration_'.$emp_model['id']];
				
				$salary->save();
			}
			$record_form->js()->reload(array(
										'branch_id'=>$record_form['branch'],
										'month'=>$record_form['month'],
										'year'=>$record_form['year'],
										'working_day'=>$record_form['working_day'],
										'filter'=>1
										)
			)->execute();

		}
	}

	function page_salaryRecord(){
	
		$month=array( 'Jan'=>"Jan",'Feb'=>"Feb",'March'=>"March",'April'=>"April",
					'May'=>"May",'Jun'=>"Jun",'July'=>"July",'Aug'=>"Aug",'Sep'=>"Sep",
					'Oct'=>"Oct",'Nov'=>"Nov",'Dec'=>"Dec");
		
		$date=$this->api->today;
		$y=date('Y',strtotime($date));	
		for ($i=$y; $i >=1970 ; $i--) { 
			$years[]=$i;
		}
		$form=$this->add('Form',null,null,array('form/horizontal'));
		$branch_field=$form->addField('Dropdown','branch')->validateNotNull(true)->setEmptyText('Please Select Branch');
		$branch_field->setModel('Branch');
		$form->addField('Dropdown','month')->setValueList($month)->validateNotNull(true)->setEmptyText('Please Select Month');
		$form->addField('Dropdown','year')->setValueList($years)->validateNotNull(true)->setEmptyText('Please Select Year');
		$form->addSubmit('Get Record');

		$salary_model=$this->add('Model_EmployeeSalary');
		$grid=$this->add('Grid');

		$this->api->stickyGET('branch');
		$this->api->stickyGET('month');
		$this->api->stickyGET('year');
		$this->api->stickyGET('filter');
		
		if($_GET['filters']){

			if($_GET['branch']){
				$salary_model->addCondition('branch_id',$_GET['branch']);
			}
			if($_GET['month']){
				$salary_model->addCondition('month',$_GET['month']);
			}
			if($_GET['year']){
				$salary_model->addCondition('year',$_GET['year']);
			}
		}else
			$salary_model->addCondition('id',-1);	

		$grid->setModel($salary_model);
		if($form->isSubmitted()){
			$grid->js()->reload(array(
								'branch_id'=>$form['branch'],
								'month'=>$form['month']?:0,
								'year'=>$form['year']?:0,
								'filters'=>1)
							)
			->execute();
		}	
	}

	function render(){
		$this->app->pathfinder->base_location->addRelativeLocation(
		    'epan-components/'.__NAMESPACE__, array(
		        'php'=>'lib',
		        'template'=>'templates',
		        'css'=>array('templates/css','templates/js'),
		        'img'=>array('templates/css','templates/js'),
		        'js'=>'templates/js',
		    )
		);

		$this->js()->_load('emp-salary');
		// $this->api->jquery->addStylesheet('xShop-js');
		// 	$this->api->template->appendHTML('js_include','<script src="epan-components/xShop/templates/js/xShop-js.js"></script>'."\n");
		parent::render();	
}
}