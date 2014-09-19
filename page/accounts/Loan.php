<?php

class page_accounts_Loan extends Page {

	function page_index(){
		$tabs=$this->add('Tabs');
		$accounts_tab = $tabs->addTabURL($this->api->url('./accounts'),'Accounts');
		$pending_accounts_tab = $tabs->addTabURL($this->api->url('./pendingAccounts'),'Pending');
	}

	function page_pendingAccounts(){
		$crud=$this->add('xCRUD');
		$account_loan_model = $this->add('Model_Account_Loan',array('table'=>'accounts_pending'));
		$account_loan_model->addField('is_approved')->type('boolean')->defaultValue(false);
		$account_loan_model->addCondition('is_approved',false);

		$account_loan_model->add('Controller_Acl');
		$account_loan_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			$loan_against_account_field = $crud->form->getElement('LoanAgainstAccount_id');
			$amount=($loan_against_account_field->model['Amount']*80)/100;
			
			if($crud->form->get('Amount')!=$amount){
				$account=$crud->add('Model_Account');
				$account->load($form['LoanAgainstAccount_id']);
				$amount=$account['Amount']*80/100;
				$loan_amount=$form['Amount'];
				if($amount<$loan_amount)
					$form->displayError('Amount',"Amount is grater than 80% of  FD Amount");
			}






			if($crud->isEditing('edit')) {
				$extra_info = json_decode($crud->form->model['extra_info'],true);
				$extra_info['loan_from_account'] = $form['loan_from_account'];
				$crud->form->model['extra_info'] = json_encode($extra_info);
				return;
			}			

			$loan_account_model = $crud->add('Model_Account_Loan');
			try {
				$crud->api->db->beginTransaction();
			    $loan_account_model->createNewPendingAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});


		/**
		 * Add Documents Fields ...
		 */

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$documents=$this->add('Model_Document');
			$documents->addCondition('LoanAccount',true);
			foreach ($documents as $d) {
			    $f1=$crud->form->addField('checkbox',$this->api->normalizeName($documents['name']));
			   	$o->move($f1,'last');
			    $f2=$crud->form->addField('line',$this->api->normalizeName($documents['name'].' value'));
			   	$o->move($f2,'last');
			   	$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array($this->api->normalizeName($documents['name'].' value'))
					),'div .atk-form-row');
			}
		}


		if($crud->isEditing()){
			$loan_from_account_field = $crud->form->addField('autocomplete/Basic','loan_from_account')->validateNotNull();
			$loan_from_account_field->setModel('Account');

		}
		


		if($crud->isEditing('edit')){
			
			// $account_loan_model->hook('editing');
		}
		
		$crud->setModel($account_loan_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account_id','LoanInsurranceDate','LoanAgainstAccount_id','dealer_id','doc_image_id','sig_image_id'),array('AccountNumber','member','scheme','Amount','agent','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account','LoanInsurranceDate','LoanAgainstAccount','dealer','doc_image','sig_image'));

		if($crud->isEditing()){			//TODO 
			$loan_against_account_field = $crud->form->getElement('LoanAgainstAccount_id');
			$loan_against_account_field->send_other_fields = array($crud->form->getElement('member_id'));
			if($member_selected = $_GET['o_'.$crud->form->getElement('member_id')->name]){
				$loan_against_account_field->model->addCondition('member_id',$member_selected);
				$loan_against_account_field->model->addCondition('ActiveStatus',true);
			}

				// throw new Exception("Error Processing Request", 1);
				

			$crud->form->getElement('account_type')->setEmptyText('Please Select');
            $loan_from_account = json_decode($crud->form->model['extra_info'],true);
			$loan_from_account = $loan_from_account['loan_from_account'];
			$crud->form->getElement('loan_from_account')->set($loan_from_account);
			
			$f1=$crud->form->getElement('account_type');
			$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'Loan Against Deposit'=>array('LoanAgainstAccount','LoanAgainstAccount_id')
					),'div .atk-form-row');

			$member_field = $crud->form->getElement('member_id');

			// $member_field->model->addExpression('count_sm')->set(function($m,$q){
			// 	return ;
			// });

			$member_field->model->_dsql()->having('('.$member_field->model->dsql()->expr($member_field->model->refSQL('Account')->addCondition('AccountNumber','like','%sm%')->count()->render().')'),'>',0); 

			$member_existing_loan_view = $member_field->other_field->belowField()->add('View');
			if($_GET['check_existing_loan_member_id']){
				$member_for_existing_loans = $this->add('Model_Member');
				$member_for_existing_loans->load($_GET['check_existing_loan_member_id']);
				$members_loan_accounts = $member_for_existing_loans->ref('Account')->addCondition('SchemeType','Loan')->count()->getOne();
				$member_existing_loan_view->set('Member Has ' . $members_loan_accounts. ' existing Loan Accounts');
			}
			$member_field->other_field->js('change',$member_existing_loan_view->js()->reload(array('check_existing_loan_member_id'=>$member_field->js()->val())));;

		}

		if($crud->isEditing('add')){
			// $crud->form->getElement('member_id')->getModel()->addOkConditions();


			$crud->form->add('Order')
						// ->move('LoanAgSecurity','after','LoanInsurranceDate')
						->move($loan_from_account_field->other_field,'after','LoanInsurranceDate')
						->now();
			$o->now();
		}


		if($crud->grid){
			$crud->grid->addClass('pending_grid');
			$crud->grid->js('reload')->reload();
			$crud->grid->addPaginator(10);
			$crud->grid->addColumn('expander','edit_pendingDocument');
			$crud->grid->addColumn('expander','edit_guarantor');
			$crud->grid->addColumn('expander','action');
		}
	}

	function page_accounts(){

		
		$crud=$this->add('xCRUD',array('allow_add'=>false,'allow_edit'=>false));
		$account_loan_model = $this->add('Model_Account_Loan');

		$account_loan_model->add('Controller_Acl');
		$account_loan_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return;
			
			if($form['LoanAgSecurity'] AND !$form['LoanAgainstAccount_id'])
				$form->displayError('LoanAgainstAccount','Please Specify Loan Against Account Number');

			$loan_account_model = $crud->add('Model_Account_Loan');
			try {
				$crud->api->db->beginTransaction();
			    $loan_account_model->createNewPendingAccount($form['member_id'],$form['scheme_id'],$crud->api->current_branch, $form['AccountNumber'],$form->getAllFields(),$form);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});


		/**
		 * Add Documents Fields ...
		 */
		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$documents=$this->add('Model_Document');
			$documents->addCondition('LoanAccount',true);
			foreach ($documents as $d) {
			    $f1=$crud->form->addField('checkbox',$this->api->normalizeName($documents['name']));
			   	$o->move($f1,'last');
			    $f2=$crud->form->addField('line',$this->api->normalizeName($documents['name'].' value'));
			   	$o->move($f2,'last');
			   	$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array($this->api->normalizeName($documents['name'].' value'))
					),'div .atk-form-row');
			}
			$loan_from_account_field = $crud->form->addField('autocomplete/Basic','loan_from_account')->validateNotNull();
			$loan_from_account_field->setModel('Account');
		}



		if($crud->isEditing('edit')){ 
			$account_loan_model->hook('editing');
		}
		
		$crud->setModel($account_loan_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','agent_id','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account_id','LoanInsurranceDate','LoanAgainstAccount_id','dealer_id'),array('AccountNumber','member','scheme','Amount','agent','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account','LoanInsurranceDate','LoanAgainstAccount','dealer'));
		$crud->add('Controller_DocumentsManager',array('doc_type'=>'LoanAccount'));
		
		if($crud->isEditing()){
			if($crud->form->hasElement('account_type')) // Removed in editing hook so may not have here
				$crud->form->getElement('account_type')->setEmptyText('Please Select');
		}

		if($crud->isEditing('add')){

			$f1=$crud->form->getElement('account_type');
			$f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'Loan Against Deposit'=>array('LoanAgainstAccount','LoanAgainstAccount_id')
					),'div .atk-form-row');

			$crud->form->add('Order')
						// ->move('LoanAgSecurity','after','LoanInsurranceDate')
						->move($loan_from_account_field->other_field,'after','LoanInsurranceDate')
						->now();
			$o->now();
		}


		if($crud->grid){
			$crud->grid->addClass('account_grid');
			$crud->grid->js('reload')->reload();
			$crud->grid->addPaginator(10);
			$crud->grid->addColumn('expander','comment');
			$crud->grid->addColumn('expander','edit_document');
			$crud->grid->addColumn('expander','edit_guarantor');
			$crud->grid->addColumn('expander','edit');
		}

	}

	function page_pendingAccounts_edit_guarantor(){
		$this->api->stickyGET('accounts_pending_id');
		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->load($_GET['accounts_pending_id']);

		$extra_info = json_decode($pending_account['extra_info'],true);
		$guarantor_info = isset($extra_info['guarantors'])?$extra_info['guarantors']:array();
		
		$form= $this->add('Form');
		$form->addField('autocomplete/Basic','member')->setModel('Member');

		$form->addSubmit('Add');

		$grid = $this->add('Grid');

		$grid->addColumn('Button','remove');

		if($_GET['remove']){

			for ($i=0; $i < count($guarantor_info); $i++) { 
				if($guarantor_info[$i]['id'] == $_GET['remove'])
					unset($guarantor_info[$i]);
			}
			$extra_info['guarantors'] = $guarantor_info;
			$pending_account['extra_info'] = json_encode($extra_info);
			$pending_account->save();
			$grid->js()->reload()->execute();

		}

		if($form->isSubmitted()){
			$found = false;
			foreach ($guarantor_info as $g) {
				if($form['member'] == $g['id']) $found=true;
			}
			if(!$found)	{
				$guarantor_info[] = array('id'=>$form['member'],'guarantor'=>$this->add('Model_Member')->load($form['member'])->get('name'));
			}
			$extra_info['guarantors'] = $guarantor_info;
			$pending_account['extra_info'] = json_encode($extra_info);
			$pending_account->save();
			$grid->js(null,$form->js()->reload())->reload()->execute();

		}

		$grid->setSource($guarantor_info);
		$grid->addColumn('id');
		$grid->addColumn('guarantor');


	}

	function page_pendingAccounts_edit_pendingDocument(){
		$this->api->stickyGET('accounts_pending_id');
		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->load($_GET['accounts_pending_id']);

		$extra_info = json_decode($pending_account['extra_info'],true);
		$doc_info =$extra_info['documents_feeded'];

		$form = $this->add('Form');
		$o=$form->add('Order');

		$documents=$this->add('Model_Document');
		$documents->addCondition('LoanAccount',true);
		foreach ($documents as $d) {
		    $f1=$form->addField('checkbox',$this->api->normalizeName($documents['name']));
		   	$o->move($f1,'last');
		    $f2=$form->addField('line',$this->api->normalizeName($documents['name'].' value'));
		   	$o->move($f2,'last');
		   	if($doc_info[$documents['name']]){
		   		$f1->set(1);
		   		$f2->set($doc_info[$documents['name']]);
		   	}
		   	$f1->js(true)->univ()->bindConditionalShow(array(
				''=>array(''),
				'*'=>array($this->api->normalizeName($documents['name'].' value'))
				),'div .atk-form-row');
		}

		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$documents=$this->add('Model_Document');
			$documents_feeded = array();
			foreach ($documents as $d) {
			 	if($form[$this->api->normalizeName($documents['name'])]){
					$doc_info[$documents['name']]=$form[$this->api->normalizeName($documents['name'].' value')];
			 	}else{
			 		unset($doc_info[$documents['name']]);
			 	}
			}
			$extra_info['documents_feeded'] = $doc_info;
			$pending_account['extra_info'] = json_encode($extra_info);
			$pending_account->save();
			$form->js()->univ()->closeExpander()->execute();

		}
	}

	function page_pendingAccounts_action(){
		$this->api->stickyGET('accounts_pending_id');
		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->load($_GET['accounts_pending_id']);
		

		$approve_btn = $this->add('Button')->set('APPROVE');
		$reject_btn = $this->add('Button')->set('REJECT');

		if($approve_btn->isClicked('Are you sure to approve and create account')){
			try{
				$this->api->db->beginTransaction();
					$pending_account->approve();

				$this->api->db->commit();
			}catch(Exception $e){
				$this->api->db->rollBack();
				throw $e;
			}
			$this->js()->_selector('.pending_grid')->trigger('reload')->execute();
		}

		if($reject_btn->isClicked('Are you sure')){
			// try {
			// 	$this->api->db->beginTransaction();
			    $pending_account->reject();
			    // $this->api->db->commit();
			// } catch (Exception $e) {
			   	// $this->api->db->rollBack();
			   	// throw $e;
			}
			$this->js()->_selector('.pending_grid')->trigger('reload')->execute();
		}

	}

	function page_accounts_edit_document(){
		$this->api->stickyGET('accounts_id');

		$documents=$this->add('Model_DocumentSubmitted');
		$documents->addCondition('accounts_id',$_GET['accounts_id']);


		$crud=$this->add('CRUD',array('allow_add'=>true));
		$crud->setModel($documents);
		if($crud->form){
			$crud->form->getElement('documents_id')->getModel()->addCondition('LoanAccount',true);
		}
	}


	function page_accounts_edit_guarantor(){
		$this->api->stickyGET('accounts_id');


		$account_guarantors=$this->add('Model_AccountGuarantor');
		$account_guarantors->addCondition('account_id',$_GET['accounts_id']);
		$account_guarantors->getElement('member_id')->caption('Guarantor');

		$crud=$this->add('CRUD');
		$crud->setModel($account_guarantors, array('member_id','member'));

	}


	function page_accounts_Loan_accounts_comment(){
		
	}
