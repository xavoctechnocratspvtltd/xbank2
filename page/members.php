<?php

class page_members extends Page {
	
	public $title='Member Management';

	function page_index(){
		// parent::init();
		
		$this->add('Controller_Acl');		

		$crud = $this->add('xCRUD',array('grid_class'=>'Grid_Member'));
		$crud->addClass('membercrud');

		$member_model = $this->add('Model_Member');
		$member_model->setOrder('member_no','desc');

		$member_model->getElement('IsMinor')->destroy();
		$member_model->getElement('ParentName')->destroy();
		$member_model->getElement('RelationWithParent')->destroy();
		$member_model->getElement('MinorDOB')->destroy();
		$member_model->getElement('Nominee')->destroy();
		
		$member_model->addExpression('sm_nominee_details')->set(function($m,$q){
			$acc = $m->add('Model_Account_SM')
						->addCondition('member_id',$m->getElement('id'))
						->setLimit(1);
			$AccountNumber = $acc->fieldQuery('AccountNumber');

			$acc = $m->add('Model_Account_SM')
						->addCondition('member_id',$m->getElement('id'))
						->setLimit(1);
			$Nominee = $acc->fieldQuery('Nominee');

			$acc = $m->add('Model_Account_SM')
						->addCondition('member_id',$m->getElement('id'))
						->setLimit(1);
			$NomineeAge = $acc->fieldQuery('NomineeAge');

			$acc = $m->add('Model_Account_SM')
						->addCondition('member_id',$m->getElement('id'))
						->setLimit(1);
			$RelationWithNominee = $acc->fieldQuery('RelationWithNominee');

			$acc = $m->add('Model_Account_SM')
						->addCondition('member_id',$m->getElement('id'))
						->setLimit(1);
			$MinorNomineeDOB = $acc->fieldQuery('MinorNomineeDOB');

			$acc = $m->add('Model_Account_SM')
						->addCondition('member_id',$m->getElement('id'))
						->setLimit(1);
			$MinorNomineeParentName = $acc->fieldQuery('MinorNomineeParentName');


			return $acc->_dsql()->expr('concat([5],"<br/>Nominee: ",[0]," <br/>Age: ",[1],"<br/>Relation: ",[2],"<br/>Minor DOB: ",[3],"<br/>Minor Parent ",[4])',[
						$Nominee,
						$NomineeAge,
						$RelationWithNominee,
						$MinorNomineeDOB,
						$MinorNomineeParentName,
						$AccountNumber
			]);
		})->allowHTML(true);

		$member_model->addExpression('shares_count')->set($member_model->refSQL('Share')->count());

		$crud->addHook('myupdate',function($crud,$form){

			if($crud->isEditing('edit')) return false;
			
			$new_member_model = $crud->add('Model_Member');
			
			$shareValue=0;
			if($form['open_share_account']) {
				if(!$form['share_account_amount'])
					$form->displayError('share_account_amount','Must Be filled');

				if($form['share_account_amount'] % 100 != 0)
					$form->displayError('share_account_amount','Must be in Multiple of 100 only');

				$shareValue = $form['share_account_amount'];
			}

			if($form['IsMinor']){
				
				$field=false;
				
				if(!$form['MinorDOB']) $field='MinorDOB';
				if(!$form['ParentName']) $field= 'ParentName';
				if(!$form['RelationWithParent']) $field = 'RelationWithParent'; 
				if(!$form['ParentAddress']) $field= 'ParentAddress';
				
				if($field) $form->displayError($field,$field.' is Mandatory');
			
			}
			
			if(!$form['form_60_61_is_submitted'] and !$form['PanNo'])
				$form->displayError('PanNo','either PanNo or Form 60/61 is required');
			
			if($form['PanNo']){

				if(!$this->isPanExists($form['PanNo'])){
					$form->displayError('PanNo','Pan Card already used');
				}
				if(!$this->isPanValid($form['PanNo'])){
					$form->displayError('PanNo','Pan Card looks wrong');
				}
			}

			if(!$this->isAadharValid($form['AdharNumber'])){
				$form->displayError('AdharNumber','AdharNumber does not looks valid');
			}

			$member_old = $this->add('Model_Member');
			$member_old->addCondition('AdharNumber',$form['AdharNumber']);
			$member_old->tryLoadAny();

			if($member_old->loaded()){
				$form->displayError('AdharNumber','Already Exists');
			}
			
			try {
				$crud->api->db->beginTransaction();
				$new_member_model->createNewMember($form['name'], $admissionFee=10, $shareValue, $branch=null, $other_values=$form->getAllFields(),$form,$on_date=null);
				
				// update form 60/61 detail
				if($form['form_60_61_is_submitted']){
					$new_member_model->submitForm60();
				}

				$crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});

		if($crud->isEditing()){
			$member_model->getElement('created_at')->system(true);
			$member_model->getElement('updated_at')->system(true);
			// $member_model->getElement('Nominee')->system(true);
			// $member_model->getElement('NomineeAge')->system(true);
			// $member_model->getElement('RelationWithNominee')->system(true);
			// $member_model->getElement('username')->system(true);
			$member_model->getElement('is_agent')->system(true);
			// $member_model->getElement('is_active')->system(true);
			$member_model->getElement('is_defaulter')->system(true);
		}

		if($crud->isEditing('edit')){
			// $member_model->getElement('username')->system(true);
			// $member_model->getElement('Nominee')->system(true);
			// $member_model->getElement('RelationWithNominee')->system(true);
			// $member_model->getElement('NomineeAge')->system(true);
			$member_model->hook('editing');
		}
		
		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		    // $crud->form->addField('CheckBox','open_share_account');
		    // $crud->form->addField('Number','share_account_amount');
		}

		if(!$this->app->auth->model->isSuper()){
			$member_model->getElement('password')->system(true);
		}

		$form_fields=null;

		if($crud->isEditing()){
			$form_fields=['branch_id','title','name','FatherName','RelationWithFatherField','Cast','landmark','tehsil','city','district','state','pin_code','CurrentAddress','Occupation','PhoneNos','DOB','PanNo','AdharNumber','gstin','bankbranch_a_id','bank_account_number_1','bankbranch_b_id','bank_account_number_2','memebr_type','Witness1Name','Witness1FatherName','Witness1Address','Witness2Name','Witness2FatherName','Witness2Address','is_active','form_60_61_is_submitted'];
		}
		
		if($crud->isEditing('edit')){
			$form_fields[]='member_no';
			if($this->app->auth->model->isSuper()){
				$form_fields[] = 'username';
				$form_fields[] = 'password';
			}

		}

		if($crud->isEditing('add')){
			$debit_account = $crud->form->addField('autocomplete/Basic','debit_account');
			
			$debit_account_model = $this->add('Model_Active_Account');
		
			$debit_account_model->addCondition(
					$debit_account_model->dsql()->orExpr()
						// ->where($debit_account_model->scheme_join->table_alias.'.name',BANK_ACCOUNTS_SCHEME)
						// ->where($debit_account_model->scheme_join->table_alias.'.name',BANK_OD_SCHEME)
						// ->where($debit_account_model->scheme_join->table_alias.'.SchemeType',ACCOUNT_TYPE_SAVING)
						// ->where($debit_account_model->scheme_join->table_alias.'.name',SUSPENCE_ACCOUNT_SCHEME)
						->where($debit_account_model->table_alias.'.AccountNumber',$this->app->current_branch['Code'].SP.'MEMBERSHIP ADVANCE AMOUNT')
						->where($debit_account_model->table_alias.'.AccountNumber',$this->app->current_branch['Code'].SP.CASH_ACCOUNT)

				);			
			// $debit_account_model->add('Controller_Acl');

			$debit_account->setModel($debit_account_model,'AccountNumber');
		}

		if($crud->isEditing('add')){
			$form = $crud->form;
			$o = $form->add('Order');
			$form->addField('checkbox','form_60_61_is_submitted');
			$o->move('form_60_61_is_submitted','after','PanNo');
		}

		$crud->setModel($member_model,$form_fields);
		$crud->add('Controller_DocumentsManager',array('doc_type'=>'MemberDocuments'));


		if(!$crud->isEditing()) {
			$g=$crud->grid;

			if($_GET['active']){
				$member_model=$this->add('Model_Member');
				$member_model->load($_GET['active']);
				$member_model->toggleActiveStatus();
				$g->js()->reload()->execute();
			}

			if($_GET['defaulter']){
				$member_model=$this->add('Model_Member');
				$member_model->load($_GET['defaulter']);
				$member_model->toggleDefaulterStatus();
				$g->js()->reload()->execute();
			}

			$g->addQuickSearch(array('member_no','branch','name','created_at','is_active','CurrentAddress','landmark','PermanentAddress','FatherName','PhoneNos','PanNo'));
			// $g->addQuickSearch(array('search_string'));
			$g->addMethod('format_removeEdit',function($grid,$field){
				if($grid->model['name'] == $grid->model->ref('branch_id')->get('Code').SP.'Default')
					$grid->current_row_html[$field]='';
			});
			$g->addMethod('format_removeDelete',function($grid,$field){
				if($grid->model['name'] == $grid->model->ref('branch_id')->get('Code').SP.'Default')
					$grid->current_row_html[$field]='';
			});

			


			if($this->api->auth->model['AccessLevel'] >=80){
				$g->addColumn('button','active','Active/DeActive');
				$g->addColumn('button','defaulter','Defaulter/Normal');
			}

			$g->addColumn('expander','comment');
			$g->addFormatter('edit','removeEdit');
			$g->addFormatter('landmark','wrap');
			// $g->addFormatter('active','activeStatus');
			$g->addFormatter('delete','removeDelete');
			$g->addPaginator(10);
			// $g->controller->importField('id');
			$g->addOrder()->move('member_no','first')->now();
			// $g->addClass('.mygrid');
			// $g->js('reload')->reload();
		}
		// if($crud->isEditing()){
		// 	$is_minor_field = $crud->form->getElement('IsMinor');
		// 	$is_minor_field->js(true)->univ()->bindConditionalShow(array(
		// 		''=>array(''),
		// 		'*'=>array('MinorDOB','ParentName','RelationWithParent','ParentAddress')
		// 		),'div .atk-form-row');

		// 	$form_60_field = $crud->form->getElement('FilledForm60');
		// 	$form_60_field->js(true)->univ()->bindConditionalShow(array(
		// 		''=>array('PanNo'),
		// 		'*'=>array()
		// 		),'div .atk-form-row');

		// }

		if($crud->isEditing('add')){

		    // $o->move('open_share_account','before','Nominee');
		    // $o->move('share_account_amount','before','Nominee');
			// $open_share_account_field = $crud->form->getElement('open_share_account');
			// $open_share_account_field->js(true)->univ()->bindConditionalShow(array(
			// 	''=>array(''),
			// 	'*'=>array('share_account_amount','Nominee','RelationWithNominee','NomineeAge')
			// 	),'div .atk-form-row');
			$o->now();
		}

		if(!$this->app->auth->model->isSuper()){
			$crud->grid->removeColumn('password');
		}

		$crud->add('Controller_Acl');
	}

	function isPanExists($value){
		return $this->add('Model_Member')->tryLoadBy('PanNo',$value)->count()->getOne();
	}

	function isPanValid($value){
		$pattern = '/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/';
		$result = preg_match($pattern, $value);
		if ($result) {
		    $findme = ucfirst(substr($value, 3, 1));
		    $mystring = 'CPHFATBLJG';
		    $pos = strpos($mystring, $findme);
		    if ($pos === false) {
		        $msg = false;
		    } else {
		        $msg = true;
		    }
		} else {
		    $msg = false;
		}
		return $msg;
	}

	function isAadharValid($num) {
		$pattern = '/^([0-9]){12}?$/';
		$result = preg_match($pattern, $num);
		return $result;
	}


	function page_comment(){
		$this->api->stickyGET('members_id');

		$member=$this->add('Model_Member');
		$member->load($_GET['members_id']);

		$show_comment_model=$this->add('Model_Comment');
		$show_comment_model->addCondition('member_id',$member->id);
		$show_comment_model->setOrder('created_at','desc');
		$show_comment_model->tryLoadAny();
			
		$show_comment=$show_comment_model->get('narration');
		// $show_comment_model->setOrder('narration','desc');
		$form=$this->add('Form');
		$form->addField('text','narration')->set($show_comment);
		$form->addSubmit('Save');
		$grid=$this->add('Grid');
		$comment=$this->add('Model_Comment');
		$comment->addCondition('member_id',$_GET['members_id']);
		$grid->setModel($comment,array('member','narration','created_at'));
		if($form->isSubmitted()){
			$comment=$this->add('Model_Comment');
			$comment->createNew($form['narration'],$member);
			$form->js(null,$grid->js()->reload())->univ()->closeExpander()->execute();
		}

	}
}