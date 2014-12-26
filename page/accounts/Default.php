<?php

class page_accounts_Default extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$account_Default_model = $this->add('Model_Account_Default');
		$account_Default_model->addCondition('scheme_name','<>','Share Capital');
		$account_Default_model->add('Controller_Acl');
		$account_Default_model->setOrder('id','desc');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$Default_account_model = $crud->add('Model_Account_Default');			
			try {
				$crud->api->db->beginTransaction();
			    $Default_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			// $k = 2;
			// for($k=2;$k<=4;$k++) {
			//     $f=$crud->form->addField('autocomplete/Basic','member_ID'.$k);
			//    	$f->setModel('Member');
			//    	$o->move($f->other_field,'before','Nominee');
			// }

			$account_Default_model->getElement('member_id')->getModel()->addCondition('is_active',true);

		}

		if($crud->isEditing('edit')){
			$account_Default_model->hook('editing');
		}

		$crud->setModel($account_Default_model,array('AccountNumber','member_id','scheme_id','ActiveStatus'),array('AccountNumber','member','scheme','ActiveStatus'));
		
		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->grid->addOrder()->move('member','first')->now();
		}

		if($crud->isEditing()){
			$m = $crud->form->getElement('scheme_id')->getModel();
			$m->addCondition('SchemeType',ACCOUNT_TYPE_DEFAULT);
			$m->addCondition('name','<>','Share Capital');
			$m->addCondition('published',true);
		}

		if($crud->isEditing('add')){
			// $o->move('initial_opening_amount','before','Amount')
			// ->now();
		}

	}
}