<?php

class page_accounts_CC extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$account_cc_model =$this->add('Model_Account_CC');
		$account_cc_model->add('Controller_Acl');		
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return;
			
			$documents=$form->add('Model_Document');
			$documents->addCondition('CCAccount',true);
			foreach ($documents as $d) {
				
			    if($form[$form->api->normalizeName($documents['name'])] and $form[$form->api->normalizeName($documents['name'].' value')]=='')
			    	$form->displayError($form->api->normalizeName($documents['name'].' value'),'Please Specify');
			}
			$cc_account_model = $crud->add('Model_Account_CC');
			
			$cc_account_model->createNewAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
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

		$crud->setModel($account_cc_model,array('AccountNumber','AccountDisplayName','member_id','scheme_id','Amount','agent_id','ActiveStatus'));

		
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

	}
}