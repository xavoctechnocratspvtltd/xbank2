<?php

class page_staff_main extends Page {
	public $title = 'Staff Management';

	function init(){
		parent::init();

		$tabs=$this->add('Tabs');
			$active_tab=$tabs->addTab('Active Staff');
				$active_tab->add('Controller_Acl');

				if($_GET['acl']){
					$this->js()->univ()->frameURL('ACL',$this->api->url('staff_acl',array('staff_id'=>$_GET['acl'])))->execute();
				}
				
				$crud=$active_tab->add('xCRUD');
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

				$crud->setModel($acticve_staff_model,array('branch','name','username','password','AccessLevel','accounts','transactions'));
				
				if(!$crud->isEditing()){
					$crud->grid->addPaginator(50);
					$crud->grid->addQuickSearch(array('name'));
					if($this->api->currentStaff->isSUper())
						$crud->grid->addColumn('Button','acl');
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

			$inactive_tab=$tabs->addTab('InActive Staff');

				$inactive_tab->add('Controller_Acl');

				if($_GET['acl']){
					$this->js()->univ()->frameURL('ACL',$this->api->url('staff_acl',array('staff_id'=>$_GET['acl'])))->execute();
				}
				
				$crud=$inactive_tab->add('xCRUD');
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

				$crud->setModel($inacticve_staff_model,array('branch','name','username','password','AccessLevel','accounts','transactions'));
				
				if(!$crud->isEditing()){
					$crud->grid->addPaginator(50);
					$crud->grid->addQuickSearch(array('name'));
					if($this->api->currentStaff->isSUper())
						$crud->grid->addColumn('Button','acl');
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