<?php

class page_staff_main extends Page {
	public $title = 'Staff Management';

	function page_index(){
		// parent::init();

		$tabs=$this->add('Tabs');
			$tabs->addTabURL('./activeStaff','Active Staff');
			$tabs->addTabURL('./inActiveStaff','InActive Staff');
	}		
	
	function page_activeStaff(){
		$this->add('Controller_Acl');

				if($_GET['acl']){
					$this->js()->univ()->frameURL('ACL',$this->api->url('staff_acl',array('staff_id'=>$_GET['acl'])))->execute();
				}
				
				$crud=$this->add('xCRUD');
				$acticve_staff_model = $this->add('Model_Staff');
				$acticve_staff_model->addCondition('is_active',true);
				// $staff_model->add('Controller_Acl');
				// $staff_model->setOrder('id','desc');
				
				$crud->addHook('myupdate',function($crud,$form){
					if($crud->isEditing('edit')) return false;
					$new_staff = $crud->add('Model_Staff');
					try {
						$crud->api->db->beginTransaction();
					    $new_staff->createNewStaff($form['name'],$form['password'],$form['AccessLevel']);
					    $crud->api->db->commit();
					} catch (Exception $e) {
					   	$crud->api->db->rollBack();
					   	throw $e;
					}
					return true;
				});


				if($crud->isEditing('edit')){
					$acticve_staff_model->hook('editing');
				}

				$acticve_staff_model->addExpression('accounts')->set($acticve_staff_model->refSQL('Account')->count());
				$acticve_staff_model->addExpression('transactions')->set($acticve_staff_model->refSQL('Transaction')->count());

				$crud->setModel($acticve_staff_model,array(),array('branch','name','username','password','is_active','AccessLevel','accounts','transactions'));
				$p=$crud->addFrame('preview');
				if($p){
					// $p->add('View')->set($_GET[$p->short_name.'_id']);
					$col=$p->add('Columns');
					$staff_model = $p->add('Model_Staff');
					$staff_model->load($_GET[$p->short_name.'_id']);
					
					$lef_col=$col->addColumn(6);
						$lef_col->add('View')->setHtml("<b> Employee Name - "." ".$staff_model['name']);
						$lef_col->add('View')->set("Employee Code - "." ".$staff_model['emp_code']);
						$lef_col->add('View')->set("Marrialtal Status - "." ".$staff_model['marriatal_status']);
						$lef_col->add('View')->set("Blood Group - "." ".$staff_model['blood_group']);
						$lef_col->add('View')->set("Nominee Name - "." ".$staff_model['nominee_name']);
						$lef_col->add('View')->set("Nominee Age- "." ".$staff_model['nominee_age']);
						$lef_col->add('View')->set("Relation With Nominee - "." ".$staff_model['relation_with_nominee']);
						$lef_col->add('View')->set("Amount of Increment - "." ".$staff_model['amount_of_increment']);
						$lef_col->add('View')->set("Yearly Increment Amount - "." ".$staff_model['yearly_increment_amount']);
						$lef_col->add('View')->set("Salary - "." ".$staff_model['salary']);
						$lef_col->add('View')->set("Active/UnActive - "." ".$staff_model['is_active']);
						$lef_col->add('View')->set("Relaving Date if Not Active - "." ".$staff_model['relaving_date_if_not_active']);
						$lef_col->add('View')->set("Remark - "." ".$staff_model['remark']);
					$rig_col=$col->addColumn(6);
						$rig_col->add('View')->set("Security Amount - "." ".$staff_model['security_amount']);
						$rig_col->add('View')->set("Deposit Date - "." ".$staff_model['deposit_date']);
						$rig_col->add('View')->set("Total Dep Amount - "." ".$staff_model['total_dep_amount']);
						$rig_col->add('View')->set("Posting At - "." ".$staff_model['posting_at']);
						$rig_col->add('View')->set("Role - "." ".$staff_model['role']);
						$rig_col->add('View')->set("Designation - "." ".$staff_model['designation']);
						$rig_col->add('View')->set("Pf No - "." ".$staff_model['pf_no']);
						$rig_col->add('View')->set("PAN No - "." ".$staff_model['pan_no']);
						$rig_col->add('View')->set("Last Qualification - "." ".$staff_model['last_qualification']);
						$rig_col->add('View')->set("Emergency No - "." ".$staff_model['emergency_no']);
						$rig_col->add('View')->set("Bank Name - "." ".$staff_model['bank_name']);
						$rig_col->add('View')->set("IFSC_CODE.- "." ".$staff_model['ifsc_code']);
						$rig_col->add('View')->set("Account No.- "." ".$staff_model['account_no']);
						$rig_col->add('View')->set("Last Date of Increment- "." ".$staff_model['last_date_of_increment']);

				}
				
				if(!$crud->isEditing()){
					$crud->grid->addPaginator(50);
					$crud->grid->addQuickSearch(array('name'));
					if($this->api->currentStaff->isSUper())
						$crud->grid->addColumn('Button','in_active');
						$crud->grid->addColumn('Button','acl');
				}

				if($_GET['in_active']){			
					$active_model=$this->add('Model_Staff')->load($_GET['in_active']);
						$active_model['is_active']=false;
						$active_model->save();
						$crud->grid->js(null,$this->js()->univ()->successMessage('Staff In Active'))->reload()->execute();
				}

				if($form=$crud->form){
					
					// $crud->form->add('Order')
					// 			// ->move($c_a_f->other_field,'after','Amount')
					// 			->move('initial_opening_amount','before','Amount')
					// 			->now();
				}
				// if($crud->isEditing('add')){
				// 	$o->now();
				// }
				$crud->add('Controller_Acl');
	}

	function page_inActiveStaff(){
		$this->add('Controller_Acl');

				if($_GET['acl']){
					$this->js()->univ()->frameURL('ACL',$this->api->url('staff_acl',array('staff_id'=>$_GET['acl'])))->execute();
				}
				
				$crud=$this->add('xCRUD');
				$inacticve_staff_model = $this->add('Model_Staff');
				$inacticve_staff_model->addCondition('is_active',false);
				// $staff_model->add('Controller_Acl');
				// $staff_model->setOrder('id','desc');
				
				$crud->addHook('myupdate',function($crud,$form){
					if($crud->isEditing('edit')) return false;
					$new_staff = $crud->add('Model_Staff');
					try {
						$crud->api->db->beginTransaction();
					    $new_staff->createNewStaff($form['name'],$form['password'],$form['AccessLevel']);
					    $crud->api->db->commit();
					} catch (Exception $e) {
					   	$crud->api->db->rollBack();
					   	throw $e;
					}
					return true;
				});


				if($crud->isEditing('edit')){
					$inacticve_staff_model->hook('editing');
				}

				$inacticve_staff_model->addExpression('accounts')->set($inacticve_staff_model->refSQL('Account')->count());
				$inacticve_staff_model->addExpression('transactions')->set($inacticve_staff_model->refSQL('Transaction')->count());

				$crud->setModel($inacticve_staff_model,array(),array('branch','name','username','password','AccessLevel','accounts','transactions'));
				
				if(!$crud->isEditing()){
					$crud->grid->addPaginator(50);
					$crud->grid->addQuickSearch(array('name'));
					if($this->api->currentStaff->isSUper())
						$crud->grid->addColumn('Button','active');
						$crud->grid->addColumn('Button','acl');
				}

				if($_GET['active']){			
					$inactive_model=$this->add('Model_Staff')->load($_GET['active']);
						$inactive_model['is_active']=true;
						$inactive_model->save();
						$crud->grid->js(null,$this->js()->univ()->successMessage('Staff In Active'))->reload()->execute();
				}

				if($form=$crud->form){
					
					// $crud->form->add('Order')
					// 			// ->move($c_a_f->other_field,'after','Amount')
					// 			->move('initial_opening_amount','before','Amount')
					// 			->now();
				}
				// if($crud->isEditing('add')){
				// 	$o->now();
				// }
				$crud->add('Controller_Acl');
	}							
}