<?php

class page_accounts_Default extends Page {
	function init(){
		parent::init();
		$this->add('Controller_Acl');

		$crud=$this->add('xCRUD');
		$account_Default_model = $this->add('Model_Account_Default');
		$account_Default_model->addCondition('scheme_name','<>','Share Capital');
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

			$Default_account_model->callApi();
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

		$account_Default_model->getElement('AccountNumber')->system(false)->editable(true)->display(array('form'=>'line'));
		$account_Default_model->getElement('scheme_id')->system(false)->editable(true);
		$account_Default_model->getElement('PAndLGroup')->system(false)->editable(true);

		$crud->setModel($account_Default_model,array('AccountNumber','member_id','scheme_id','ActiveStatus','sig_image_id','PAndLGroup'),array('AccountNumber','scheme','ActiveStatus','member','created_at'));
		$crud->add('Controller_DocumentsManager',array('doc_type'=>'OtherAccounts'));
		
		if(!$crud->isEditing()){
			$crud->grid->addPaginator(100);
			$crud->grid->addQuickSearch(array('AccountNumber'));
			// $crud->grid->addOrder()->move('member','first')->now();
		}

		if($crud->isEditing('add')){
			$crud->form->getElement('member_id')->getModel()->addCondition('is_active',true);
			$m = $crud->form->getElement('scheme_id')->getModel();
			$m->addCondition('SchemeType',ACCOUNT_TYPE_DEFAULT);
			$m->addCondition('name','<>','Share Capital');
			$m->addCondition('published',true);
			$m->putValidDateCondition();
		}

		if($crud->isEditing('add')){
			// $o->move('initial_opening_amount','before','Amount')
			// ->now();
		}
		$crud->add('Controller_Acl');

	}
}