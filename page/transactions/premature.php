<?php

class page_transactions_premature extends Page {
	public $title ='Pre Mature Payments';

	function init(){
		parent::init();
		$this->add('Controller_Acl',array('default_view'=>false));
		
		// Deactivate Account Remove
		$account_model=$this->add('Model_Account',array('table_alias'=>'acc'));
		
		// Loan PL and VL not allowed
		$account_model->addCondition('SchemeType','<>',ACCOUNT_TYPE_LOAN);
		$account_model->addCondition('SchemeType',array(ACCOUNT_TYPE_FIXED,ACCOUNT_TYPE_RECURRING,ACCOUNT_TYPE_DDS));
		$account_model->addCondition('ActiveStatus',true);
		// CC Allowed


		// In Case of No Image ... Stop withdrawl == Done in withdrawl function in modelaccount		
		$cols= $this->add('Columns');
		$left_col = $cols->addColumn(6);
		$right_col = $cols->addColumn(6);
		$form = $left_col->add('Form',null,null,['form/stacked']);
		$account_field = $form->addField('autocomplete/Basic',array('name'=>'account'))->validateNotNull();
		// $account_model->filter(array($account_model->scheme_join->table_alias.'.SchemeGroup'=>array('%Bank Accounts%','%Suspence Account%','%Cash Account%','%Branch & Divisions%'),$account_model->table_alias.'.account_type'=>array('%Saving%','%Current%')));
		$account_field->setModel($account_model,'AccountNumber');
		$account_mode_view = $account_field->belowField()->add('View');
		
		$amount_field_ro = $form->addField('Readonly','maturity_amount');
		$form->add('HR');

		$pandl_accounts= $this->add('Model_Account');
		$pandl_accounts->scheme_join->join('balance_sheet','balance_sheet_id')->addField('is_pandl');
		$pandl_accounts->addCondition('is_pandl',true);
		
		$c= $form->add('Columns');
		$c1 = $c->addColumn(6);
		$c2 = $c->addColumn(6);
		$charges_account_field = $form->addField('autocomplete/Basic','charges_account');
		$charges_account_field->setModel($pandl_accounts,'AccountNumber');
		$charges_amount_field = $form->addField('charges_amount');

		// $c1->add($debit_account_field->other_field);
		// $c2->add($debit_amount_field);

		$form->add('HR');
		$c= $form->add('Columns');
		$c3 = $c->addColumn(6);
		$c4 = $c->addColumn(6);
		$bonus_account_field = $form->addField('autocomplete/Basic','bonus_account');
		$bonus_account_field->setModel($pandl_accounts,'AccountNumber');
		$bonus_amount_field = $form->addField('bonus_amount');

		// $c3->add($credit_account_field->other_field);
		// $c4->add($credit_amount_field);

		$account_to_credit_model = $this->add('Model_Active_Account');
		$account_to_credit_model->addCondition('branch_id',$this->app->current_branch->id);
		$account_to_credit_model->addCondition([['scheme_name',[CASH_ACCOUNT_SCHEME,BANK_ACCOUNTS_SCHEME,BANK_OD_SCHEME]],['SchemeType',ACCOUNT_TYPE_BANK]]);
		$account_to_credit_field = $form->addField('autocomplete/Basic','account_to_credit')->validateNotNull();
		$account_to_credit_field->setModel($account_to_credit_model,'AccountNumber');

		$form->addField('Text','narration');
		$form->addSubmit('Execute');

		if($_GET['account_selected']){

			$account = $this->add('Model_Account')->tryLoadBy('AccountNumber',$_GET['account_selected']);

			$scheme_type = $account->ref('scheme_id')->get('SchemeType');
			if($scheme_type == "DDS" && $account->ref('scheme_id')->get('type') == "DDS2")
				$scheme_type = "DDS2";

			$account_model = $this->add('Model_Account_'.$scheme_type);
			$account_model->loadBy('AccountNumber',$account['AccountNumber']);
			if($account->loaded()){
				$right_col->add('H3')->set(array('Signature For - '));
				$right_col->add('View')->set($_GET['account_selected']);
				$right_col->add('View')->setHtml('Account Mode: ' . $account['ModeOfOperation'] . ($account['ModeOfOperation'] == 'Joint'? '<font color=red> Check All Signatures </font>':''));

				// Show Pre-mature financial information
				$p_info = $account_model->pre_mature_info();

				$right_col->add('View')->setHTML("<b>Amount</b> : " . $account['Amount']);
				$right_col->add('View')->setHTML("<b>Scheme Pre Mature Percentage</b> : " . $p_info['pre_maturity_percentages_string']);
				$right_col->add('View')->setHTML('<b>Account Opening Date </b> : '.$account['created_at']);
				$right_col->add('View')->setHTML('<b>No Of Days/Month </b> : '. $p_info['days_months_total']);
				$right_col->add('View')->setHTML('<b>Applicable Percentage </b> : '. $p_info['applicable_percentage'].'%');
				$right_col->add('View')->setHTML('<b>Pre Maturity </b> : '. ($p_info['can_premature']?'<font color="green">YES</font>':'<font color="red">NO</font>') );
				$right_col->add('View')->setHTML('<b>Premium Paid (IF RD) </b> : '. ($p_info['premiums_paid']) );


				//$_document = $this->add('Model_DocumentSubmitted');
				//$_document->loadBy('accounts_id',$account['id']);
				$_document = $this->add('Model_DocumentSubmitted')->tryLoadBy('accounts_id',$account['id']);
				$_document->addCondition('documents','like','%Gift%');
				if($_document->loaded()){
					$right_col->add('H3')->set(array('Document Details - '));
					$right_col->add('View')->setHTML('<b>Type </b> : '. $_document['documents']);
					$right_col->add('View')->setHTML('<b>Message</b> : '. $_document['Description']);
					$right_col->add('View')->setHTML('<b>Doc Image</b> : <img src="'.$_document['doc_image'].'" />');					
					$right_col->add('View')->setHTML('<b>Uploaded On</b> : '. $_document['submitted_on']);					
				}
				

				// Show Signature image
				// $img=$right_col->add('View')->setElement('img')->setAttr('src','../signatures/sig_'.$account->ref('member_id')->get('id').'.JPG');

				$img=$right_col->add('View')->setElement('img')->setAttr('src',$account['sig_image']);
				// $img->js('mouseover',$img->js()->width('200%'));
				// $img->js('mouseout',$img->js()->width('100%'));

				// Show loan against this account if present
				if($loan_ag_this_acc = $account_model->runningLoanAccountsAgainstAccount()){
					$right_col->add('View_Error')->set('Loan Against Deposit: ' . $loan_ag_this_acc['AccountNumber']);
					return;
				}
				$account_field->other_field->set($_GET['account_selected']);
				$account_field->set($account->id);
				var_dump(get_class($account_model));
				$right_col->js(true,$amount_field_ro->js()->html($account_model->pre_mature($this->app->today,true)),0);
			}
		}else{
			$right_col->add('View_Error')->set('Select Account for Pre-Mature');
		}


		$js=array(
				$right_col->js()->reload(array('account_selected'=>$account_field->js()->val())),
			);
		$account_field->other_field->js('change',$js);
		$account_field->js('change',$js);

		if($form->isSubmitted()){
			try{		
				$account_model_temp = $this->add('Model_Account')
											->loadBy('AccountNumber',$form['account']);

				if(!$account_model_temp->loaded())
					$form->displayError('amount','Oops');

				$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
				$account_model->loadBy('AccountNumber',$form['account']);

				// Show error if loan against deposit is running
				if($loan_ag_this_acc = $account_model->runningLoanAccountsAgainstAccount()){
					$form->displayError('account','Loan Against Deposit: ' . $loan_ag_this_acc['AccountNumber']);
				}				

				$account_to_credit = $this->add('Model_Account')->loadBy('AccountNumber',$form['account_to_credit']);

				if($account_to_credit['SchemeType']=='SavingAndCurrent'){
					if($account_to_credit['member_id'] != $account_model['member_id'])
						$form->displayError('account_to_credit','Please select account of same member');
				}

				try {
					$this->api->db->beginTransaction();
						$charges_if_any=[];
						if($form['charges_account']){
							$charges_if_any['Account'] = $form['charges_account'];
							$charges_if_any['Amount'] = $form['charges_amount'];
						}

						$bonus_if_any=[];
						if($form['bonus_account']){
							$bonus_if_any['Account'] = $form['bonus_account'];
							$bonus_if_any['Amount'] = $form['bonus_amount'];
						}

						if(count($charges_if_any) && count($bonus_if_any)){
							$form->displayError('bonus_account','Please provide only either Charges or Bonus');
						}

						$amount = $account_model->pre_mature($this->app->today,false,$account_to_credit,$charges_if_any,$bonus_if_any);
				    $this->api->db->commit();
				} catch (Exception $e) {
				   	$this->api->db->rollBack();
				   	throw $e;
				}
				$js=array($form->js()->reload(),$right_col->js()->reload());
				$form->js(null,$js)->univ()->successMessage($form['amount']."/- withdrawn from " . $form['account'])->execute();
			}catch(Exception_ValidityCheck $e){
				$form->displayError($e->getField(),$e->getMessage());
			}
		}
	}
}