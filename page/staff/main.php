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