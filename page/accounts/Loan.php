<?php

class page_accounts_Loan extends Page {

	function page_index(){
		$tabs=$this->add('Tabs');
		$pending_accounts_tab = $tabs->addTabURL($this->api->url('./pendingAccounts'),'Pending');
		$accounts_tab = $tabs->addTabURL($this->api->url('./accounts'),'Accounts');
		$bike_legal = $tabs->addTabURL($this->api->url('accounts_Loan_bikelegal'),'Manage Surrender Bike and Legal');
		$legal_hearing = $tabs->addTabURL($this->api->url('accounts_Loan_casehearing'),'Manage Legal Case & Hearing');
	}

	function page_pendingAccounts(){
		$this->add('Controller_Acl');
		
		$this->app->skipUpdateTransactions = true; // for Model_Account beforeSave hook

		$crud=$this->add('xCRUD',array('allow_del'=>false,'add_form_beautifier'=>false));
		$account_loan_model = $this->add('Model_PendingAccount',array('table'=>'accounts_pending'));
		// $account_loan_model->addField('is_approved')->type('boolean')->defaultValue(false);
		$account_loan_model->addCondition('is_approved',0); // Status asked as Pending

		$account_loan_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){

			$member_check_for_sm = $crud->add('Model_Member');
			$member_check_for_sm->load($form['member_id']);
			if($member_check_for_sm->ref('Account')->addCondition('AccountNumber','like','%sm%')->count()->getOne() <= 0 ){
				$form->displayError('member_id','Not an SM');
			}
			
			if($crud->form->get('account_type') == 'Loan Against Deposit'){
				
				$account=$crud->add('Model_Account');
				$account->load($form['LoanAgainstAccount_id']);

				$account = $crud->add('Model_Account_'.$account['SchemeType']);
				// $account->scheme_join->addField('percent_loan_on_deposit');
				$account->load($form['LoanAgainstAccount_id']);

				$amount_on = $account['Amount'];
				if($account->isDDS() or $account->isRecurring()){
					$amount_on = $account->creditedAmount();
				}

				if(strtotime($crud->app->now) < strtotime($account['locked_to_loan_till'])){
					$form->displayError('LoanAgainstAccount_id','You can not loan against this account before ' . $account['locked_to_loan_till']);
				}

				$amount_allowed = $amount_on*$account['percent_loan_on_deposit']/100;
				$loan_amount=$form['Amount'];

				if($form['NomineeAge'] And  $form['NomineeAge']<18){
					$form->displayError('MinorNomineeParentName','mandatory field');
				}
				if($loan_amount > $amount_allowed)
					$form->displayError('Amount',"Amount is grater than ".$account['percent_loan_on_deposit']."% of  FD Amount (".$amount_allowed.")");
			}

			if($form['sm_amount'] && !is_numeric($form['sm_amount']))
				$form->displayError('sm_amount',"Must be a number");

			if(!$form['other_account'] && $form['other_account_cr_amount']){
				$form->displayError('other_account',"Must be filled");
			}

			if(!$form['other_account_cr_amount'] && $form['other_account']){
				$form->displayError('other_account_cr_amount',"please fill other_account_cr_amount");
			}

			if($crud->isEditing('edit')) {
				$extra_info = json_decode($crud->form->model['extra_info'],true);
				$extra_info['loan_from_account'] = $form['loan_from_account'];
				$extra_info['sm_amount'] = $form['sm_amount'];
				$extra_info['other_account'] = $form['other_account'];
				$extra_info['other_account_cr_amount'] = $form['other_account_cr_amount'];
				$crud->form->model['extra_info'] = json_encode($extra_info);
				// $crud->form->model->debug()->saveAs('Account');
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

		if($crud->isEditing()){
			$o=$crud->form->add('Order');
		}

		if($crud->isEditing("add")){
		    
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

			$account_loan_model->getElement('member_id')->getModel()->addCondition('is_active',true);
			$account_loan_model->getElement('dealer_id')->getModel()->addCondition('ActiveStatus',true);
		}


		if($crud->isEditing()){
			$loan_from_account_model =$this->add('Model_Active_Account');
			// $loan_from_account_model->join('schemes','scheme_id')->addField('scheme_name','name');

			$loan_from_account_model->addCondition(
					$loan_from_account_model->dsql()->orExpr()
						->where($loan_from_account_model->scheme_join->table_alias.'.name',BANK_ACCOUNTS_SCHEME)
						->where($loan_from_account_model->scheme_join->table_alias.'.name',BANK_OD_SCHEME)
						->where($loan_from_account_model->scheme_join->table_alias.'.SchemeType',ACCOUNT_TYPE_SAVING)
						->where($loan_from_account_model->scheme_join->table_alias.'.SchemeType',ACCOUNT_TYPE_CC)
						->where($loan_from_account_model->scheme_join->table_alias.'.name',CASH_ACCOUNT_SCHEME)
						->where($loan_from_account_model->scheme_join->table_alias.'.name','Sundry Debtor')
						->where($loan_from_account_model->scheme_join->table_alias.'.name','Loan Advance(Assets)')
						->where(
								$loan_from_account_model->dsql()->andExpr()
									->where($loan_from_account_model->scheme_join->table_alias.'.SchemeType',ACCOUNT_TYPE_LOAN)
									->where($loan_from_account_model->table_alias.'.account_type','Other')
								)
				);


			$loan_from_account_field = $crud->form->addField('autocomplete/Basic','loan_from_account')->validateNotNull();
			$loan_from_account_field->setModel($loan_from_account_model);
			$account_loan_model->getElement('ModeOfOperation')->system(true);

			$crud->form->addField('sm_amount');
			$other_account_autocomplete = $crud->form->addField('autocomplete/Basic','other_account');
			$other_account_autocomplete->setModel('Model_Account_Default');
			// $field_cr_percentage =  $crud->form->addField('other_account_cr_amount_percentage')->setFieldHint('value in %, put 10,15 etc');
			$field_cr_amount = $crud->form->addField('other_account_cr_amount');
		}
		


		if($crud->isEditing('edit')){
			
			// $account_loan_model->hook('editing');
		}
		
		$crud->setModel($account_loan_model,array('account_type','AccountNumber','member_id','scheme_id','Amount','sm_amount','agent_id','repayment_mode','insurance_tenure','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','loan_from_account_id','LoanInsurranceDate','LoanAgainstAccount_id','dealer_id','doc_image_id','sig_image_id'),array('AccountNumber','branch','created_at','member','scheme','Amount','agent','repayment_mode','insurance_tenure','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','LoanInsurranceDate','LoanAgainstAccount','dealer','doc_image','sig_image'));

		if($crud->isEditing()){			//TODO 
			$loan_against_account_field = $crud->form->getElement('LoanAgainstAccount_id');
			$loan_against_account_field->send_other_fields = array($crud->form->getElement('member_id'));
			if($member_selected = $_GET['o_'.$crud->form->getElement('member_id')->name]){
				$loan_against_account_field->model->addCondition('member_id',$member_selected);
				$loan_against_account_field->model->addCondition('ActiveStatus',true);
				$loan_against_account_field->model->addCondition('LockingStatus',false);
			}

			
			$extra_info_sm_amount = json_decode($crud->form->model['extra_info'],true);
			$sm_amount = $extra_info_sm_amount['sm_amount'];
			$crud->form->getElement('sm_amount')->set($sm_amount);

			$other_account_filled = $extra_info_sm_amount['other_account'];
			$other_account_filled_cr_amount = $extra_info_sm_amount['other_account_cr_amount'];
			// $other_account_cr_amount_percentage = $extra_info_sm_amount['other_account_cr_amount_percentage'];
			
			$crud->form->getElement('other_account')->set($other_account_filled);
			$crud->form->getElement('other_account_cr_amount')->set($other_account_filled_cr_amount);
			// $crud->form->getElement('other_account_cr_amount_percentage')->set($other_account_cr_amount_percentage);

			// 	$account_model=$this->add('Model_Active_Account');

			// // NO BANK FOR ANY BRANCH
			// $account_model->addCondition('scheme_name','<>',BANK_ACCOUNTS_SCHEME);
			// $account_model->addCondition('scheme_name','<>',BANK_OD_SCHEME);
			
			// // NO CASH FOR ANY BRANCH
			// $account_model->addCondition('scheme_name','<>',CASH_ACCOUNT_SCHEME);
			
			// // NO DEFAULT ACCOUNTS FOR OTHER BRANCH
			// $account_model->addCondition(
			// 	$account_model->dsql()->orExpr()
			// 		->where('branch_id',$this->api->current_branch->id)
			// 		->where('DefaultAC',false)
			// 	);
			
			// // No Fixed and Mis Accounts For Any Branch
			// $account_model->addCondition('SchemeType','<>',ACCOUNT_TYPE_FIXED);

			$t = $crud->form->getElement('account_type');

			// don't knwo why during editing this field is considered as line 
			// if($crud->isEditing('add')) $t->setEmptyText('Please Select');

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

			// $member_field->getModel()->debug()->_dsql()->having('('.$member_field->model->dsql()->expr($member_field->model->refSQL('Account')->addCondition('AccountNumber','like','%sm%')->count()->render().')'),'>',0); 

			$member_existing_loan_view = $member_field->other_field->belowField()->add('View');
			if($_GET['check_existing_loan_member_id']){
				$member_for_existing_loans = $this->add('Model_Member');
				$member_for_existing_loans->load($_GET['check_existing_loan_member_id']);
				$members_loan_accounts = $member_for_existing_loans->ref('Account')->addCondition('SchemeType','Loan')->addCondition('ActiveStatus',true)->count()->getOne();
				$member_existing_loan_view->set('Member Has ' . $members_loan_accounts. ' existing Loan Accounts');
			}
			$member_field->other_field->js('change',$member_existing_loan_view->js()->reload(array('check_existing_loan_member_id'=>$member_field->js()->val())));;

		}

		if($crud->isEditing('add')){
			// $crud->form->getElement('member_id')->getModel()->addOkConditions();
			$crud->form->getElement('member_id')->getModel()->addCondition('is_active',true);
			$crud->form->getElement('scheme_id')->getModel()->addCondition('ActiveStatus',true);
			$crud->form->getElement('scheme_id')->getModel()->putValidDateCondition();
			
			// $crud->form->add('Order')
						// ->move('LoanAgSecurity','after','LoanInsurranceDate')
			
			$o->move($loan_from_account_field->other_field,'after','LoanInsurranceDate');
						
		}

		if($crud->isEditing()){
			$o
				->move('sm_amount','after','Amount')
				->move('other_account','after','sm_amount')
				->move($other_account_autocomplete->other_field,'after','other_account')
				->move('other_account_cr_amount','after','other_account')
				// ->move('other_account_cr_amount_percentage','after','other_account')
				->now();
		}


		if(!$crud->isEditing()){
			$crud->grid->addClass('pending_grid');
			$crud->grid->js('reload')->reload();
			$crud->grid->addPaginator(10);
			$crud->grid->addColumn('expander','edit_pendingDocument');
			$crud->grid->addColumn('expander','edit_guarantor');
			if($this->api->auth->model['AccessLevel']>=80)
				$crud->grid->addColumn('expander','action');
			$crud->grid->addQuickSearch(array('name'));

			// Not editing
			$crud->grid->addMethod('format_loan_from_account',function($g,$f){
				$extra_info = json_decode($g->model['extra_info']);
				$g->current_row[$f]= $g->add('Model_Account')->tryLoad($extra_info->loan_from_account?:-1)->get('AccountNumber');
			});	

			// Not editing
			$crud->grid->addMethod('format_sm_amount',function($g,$f){
				$extra_info = json_decode($g->model['extra_info']);
				$g->current_row[$f]= isset($extra_info->sm_amount)?$extra_info->sm_amount:'';
			});

			// Not editing
			$crud->grid->addMethod('format_other_account',function($g,$f){
				$extra_info = json_decode($g->model['extra_info']);
				$g->current_row[$f]= $this->add('Model_Account')->tryLoad(isset($extra_info->other_account)?$extra_info->other_account:'0')->get('AccountNumber');
			});

			// Not editing
			$crud->grid->addMethod('format_other_account_cr_amount',function($g,$f){
				$extra_info = json_decode($g->model['extra_info']);
				$g->current_row[$f]= isset($extra_info->other_account_cr_amount)?$extra_info->other_account_cr_amount:'';
			});	

			// Not editing
			// $crud->grid->addMethod('format_other_account_cr_amount_percentage',function($g,$f){
			// 	$extra_info = json_decode($g->model['extra_info']);
			// 	$g->current_row[$f]= isset($extra_info->other_account_cr_amount_percentage)?$extra_info->other_account_cr_amount_percentage:'';
			// });		

			$crud->grid->addColumn('loan_from_account','loan_from_account');
			$crud->grid->addColumn('sm_amount','sm_amount');
			$crud->grid->addColumn('other_account','other_account');
			// $crud->grid->addColumn('other_account_cr_amount_percentage','other_account_cr_amount_percentage');
			$crud->grid->addColumn('other_account_cr_amount','other_account_cr_amount');

			$ox=$crud->grid->addOrder();
			$ox->move('loan_from_account','after','dealer')
				->move('sm_amount','after','Amount')
				->move('other_account','after','sm_amount')
				// ->move('other_account_cr_amount_percentage','after','other_account')
				->move('other_account_cr_amount','after','other_account');
			if($this->api->auth->model['AccessLevel']>=80)
				$ox->move('action','first');
			$ox->now();

		}

		$crud->add('Controller_FormBeautifier');
		$crud->add('Controller_Acl');
	}

	function page_accounts(){
		$this->add('Controller_Acl');
		$crud=$this->add('xCRUD',array('allow_add'=>false,'grid_class'=>'Grid_Account'));

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



		// if($crud->isEditing('edit')){
		// 	// $account_loan_model->hook('editing');
		// }
		
		$crud->setModel($account_loan_model,array('account_type','AccountNumber','member_id'/*,'scheme_id'*//*,'Amount'*/,'agent_id','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo'/*,'ModeOfOperation'*/,'loan_from_account_id'/*,'LoanInsurranceDate'*/,'LoanAgainstAccount_id','dealer_id','repayment_mode'),array('AccountNumber','created_at','member','scheme','Amount','agent','ActiveStatus','gaurantor','gaurantorAddress','gaurantorPhNo','ModeOfOperation','loan_from_account','LoanInsurranceDate','LoanAgainstAccount','dealer'));
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


		if(!$crud->isEditing()){
			$crud->grid->addClass('account_grid');
			$crud->grid->js('reload')->reload();
			$crud->grid->addColumn('expander','comment');
			// $crud->grid->addColumn('expander','edit_document');
			$crud->grid->addColumn('expander','edit_guarantor');
			// $crud->grid->addColumn('expander','edit');
			// $crud->grid->addColumn('button','edit');
			$crud->grid->addQuickSearch(array('AccountNumber'));
		}

		$crud->add('Controller_Acl');

	}

	function page_pendingAccounts_edit_guarantor(){
		$this->add('Controller_Acl');
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
		$this->add('Controller_Acl');
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
		$this->add('Controller_Acl');
		$this->api->stickyGET('accounts_pending_id');
		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->load($_GET['accounts_pending_id']);
		

		$approve_btn = $this->add('Button')->set('APPROVE');
		$reject_btn = $this->add('Button')->set('REJECT');

		if($approve_btn->isClicked('Are you sure to approve and create account')){
			try{
				$this->api->db->beginTransaction();
					$new_account = $pending_account->approve();
				$this->api->db->commit();
			}catch(Exception $e){
				$this->api->db->rollBack();
				throw $e;
			}

			$new_account->callApi();

			$this->js()->_selector('.pending_grid')->trigger('reload')->execute();
		}

		if($reject_btn->isClicked('Are you sure')){
			try {
				$this->api->db->beginTransaction();
			    $pending_account->reject();
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
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

}
