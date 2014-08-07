<?php

class page_accounts_CC extends Page {
	function page_index(){

		$crud=$this->add('xCRUD');
		$account_cc_model =$this->add('Model_Account_CC');
		$account_cc_model->add('Controller_Acl');		
		$account_cc_model->setOrder('created_at','desc');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$documents=$form->add('Model_Document');
			$documents->addCondition('CCAccount',true);
			foreach ($documents as $d) {
				
			    if($form[$form->api->normalizeName($documents['name'])] and $form[$form->api->normalizeName($documents['name'].' value')]=='')
			    	$form->displayError($form->api->normalizeName($documents['name'].' value'),'Please Specify');
			}

			$cc_account_model = $crud->add('Model_Account_CC');
			
			try {
				$this->api->db->beginTransaction();
			    $cc_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$k = 1;
			$documents=$this->add('Model_Document');
			$documents->addCondition('CCAccount',true);
			foreach ($documents as $d) {
			    $f1=$crud->form->addField('checkbox',$this->api->normalizeName($documents['name']));
			   	$o->move($f1,'last');
			    $f2=$crud->form->addField('line',$this->api->normalizeName($documents['name'].' value'));
			   	$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array($this->api->normalizeName($documents['name'].' value'))
					),'div .atk-form-row');
			   	$o->move($f2,'last');
			    $k++;
			}

		}

		if($crud->isEditing('edit')){
			$account_cc_model->hook('editing');
		}

		$crud->setModel($account_cc_model,array('AccountNumber','AccountDisplayName','member_id','scheme_id','Amount','agent_id','ActiveStatus','CurrentInterest','LastCurrentInterestUpdatedAt'),array('AccountNumber','AccountDisplayName','member','scheme','Amount','agent','ActiveStatus','CurrentInterest','LastCurrentInterestUpdatedAt'));

		
		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber','AccountDisplayName','member','scheme'));
			$crud->grid->addColumn('expander','edit_document');
		}

		if($crud->isEditing('add')){
			$o->now();
		}

	}

	function page_edit_document(){
		$this->api->stickyGET('accounts_id');

		$documents=$this->add('Model_DocumentSubmitted');
		$documents->addCondition('accounts_id',$_GET['accounts_id']);

		$crud=$this->add('CRUD',array('allow_add'=>true));
		$crud->setModel($documents);
		if($crud->form){
			$crud->form->getElement('documents_id')->getModel()->addCondition('CCAccount',true);
		}
	}

}