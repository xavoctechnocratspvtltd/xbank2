<?php

class page_accounts_CC extends Page {
	function page_index(){

		$this->add('Controller_Acl');

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Account','add_form_beautifier'=>false));
		$account_cc_model =$this->add('Model_Account_CC');
			
		$account_cc_model->setOrder('created_at','desc');

		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			
			$documents=$form->add('Model_Document');
			$documents->addCondition('CCAccount',true);
			foreach ($documents as $d) {
				
			    if($form[$form->api->normalizeName($documents['name'])] and $form[$form->api->normalizeName($documents['name'].' value')]=='')
			    	$form->displayError($form->api->normalizeName($documents['name'].' value'),'Please Specify');
			}

			if(!$form['sig_image_id']){
				$form->displayError('sig_image_id','Signature File is must to provide');
			}

			$cc_account_model = $crud->add('Model_Account_CC');
			
			try {
				$crud->api->db->beginTransaction();
			    $cc_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}

			$cc_account_model->callApi();
			
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

			$account_cc_model->getElement('member_id')->getModel()->addCondition('is_active',true);
		}

		if($crud->isEditing('edit')){
			$account_cc_model->hook('editing');
		}

		$crud->setModel($account_cc_model,array('AccountNumber','AccountDisplayName','member_id','scheme_id','Amount','agent_id','ActiveStatus','sig_image_id'),array('AccountNumber','created_at','AccountDisplayName','member','scheme','Amount','agent','ActiveStatus','CurrentInterest','LastCurrentInterestUpdatedAt'));
		$crud->add('Controller_DocumentsManager',array('doc_type'=>'CCAccount'));
		
		if(!$crud->isEditing()){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('AccountNumber','AccountDisplayName','member','scheme'));
			$crud->grid->addColumn('expander','edit_document');
			$crud->grid->addColumn('expander','edit_guarantors');
		}

		if($crud->isEditing('add')){
			$crud->form->getElement('member_id')->getModel()->addCondition('is_active',true);
			$crud->form->getElement('scheme_id')->getModel()->addCondition('ActiveStatus',true);
			$crud->form->getElement('scheme_id')->getModel()->putValidDateCondition();
			$o->now();
		}

		$crud->add('Controller_Acl');

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

	function page_edit_guarantors(){
		$this->api->stickyGET('accounts_id');


		$account_guarantors=$this->add('Model_AccountGuarantor');
		$account_guarantors->addCondition('account_id',$_GET['accounts_id']);
		$account_guarantors->getElement('member_id')->caption('Guarantor');

		$crud=$this->add('CRUD');
		$crud->setModel($account_guarantors, array('member_id','member'));

	}

}