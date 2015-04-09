<?php

class page_staff_main extends Page {
	public $title = 'Staff Management';

	function init(){
		parent::init();
		$this->add('Controller_Acl');

		if($_GET['acl']){
			$this->js()->univ()->frameURL('ACL',$this->api->url('staff_acl',array('staff_id'=>$_GET['acl'])))->execute();
		}
		
		$crud=$this->add('xCRUD');
		$staff_model = $this->add('Model_Staff');
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
			$staff_model->hook('editing');
		}

		$staff_model->addExpression('accounts')->set($staff_model->refSQL('Account')->count());
		$staff_model->addExpression('transactions')->set($staff_model->refSQL('Transaction')->count());

		$crud->setModel($staff_model,array('branch','name','username','password','AccessLevel','accounts','transactions'));
		
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