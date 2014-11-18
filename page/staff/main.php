<?php

class page_staff_main extends Page {
	function init(){
		parent::init();
		
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

		$crud->setModel($staff_model);
		
		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('name'));
			
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


	}
}