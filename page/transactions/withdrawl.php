<?php

class page_transactions_withdrawl extends Page {
	public $title ='WithDrawl Amount';
	
	function init(){
		parent::init();
		$this->add('Controller_Acl');
		
		// Deactivate Account Remove
		$account_model=$this->add('Model_Account',array('table_alias'=>'acc'));
		
		// Loan PL and VL not allowed
		$account_model->addCondition('SchemeType','<>',ACCOUNT_TYPE_LOAN);
		// No withdrawl from SM accounts also, use share buy back and transfer transactions
		$account_model->addCondition('scheme_name','<>','Share Capital');
		// CC Allowed

		// Recurring, MIS, FD and DDS only if deactivated
		$account_model->addCondition(
			$account_model->dsql()->orExpr()
				->where('(acc.ActiveStatus = 1 AND _s.SchemeType not in ("'.ACCOUNT_TYPE_FIXED.'","'.ACCOUNT_TYPE_RECURRING.'","'.ACCOUNT_TYPE_DDS.'"))')
				->where('(acc.ActiveStatus = 0 AND _s.SchemeType in ("'.ACCOUNT_TYPE_FIXED.'","'.ACCOUNT_TYPE_RECURRING.'","'.ACCOUNT_TYPE_DDS.'"))')
			);

		// In Case of No Image ... Stop withdrawl == Done in withdrawl function in modelaccount
		
		$cols= $this->add('Columns');
		$left_col = $cols->addColumn(6);
		$right_col = $cols->addColumn(6);
		$form = $left_col->add('Form');
		$account_field = $form->addField('autocomplete/Basic',array('name'=>'account'))->validateNotNull();
		// $account_field->other_field->js(true)->focus();
		$plus_btn=$account_field->other_field->afterField()->add('Button')->set(array('icon'=>'plus',''));	
		// $account_model->filter(array($account_model->scheme_join->table_alias.'.SchemeGroup'=>array('%Bank Accounts%','%Suspence Account%','%Cash Account%','%Branch & Divisions%'),$account_model->table_alias.'.account_type'=>array('%Saving%','%Current%')));
		$account_field->setModel($account_model,'AccountNumber');
		$account_mode_view = $account_field->belowField()->add('View');

		$amount_field = $form->addField('Number','amount')->validateNotNull();
		$amount_field_view = $amount_field->belowField()->add('View');

		if($_GET['check_min']){
			$acc_bal_temp = $this->add('Model_Account_SavingAndCurrent');
			$acc_bal_temp->tryLoadBy('AccountNumber',$_GET['AccountNumber']);

			if($acc_bal_temp->loaded()){
				$bal = $acc_bal_temp->getOpeningBalance();
				$bal_cr = $bal['Cr'] - $bal['Dr'];
				if($bal_cr- $_GET['amount'] < 200 AND $bal_cr- $_GET['amount'] > 0 ) $amount_field_view->set('Bellow');
			}

		}

		$amount_field->js('change',$amount_field_view->js()->reload(array('check_min'=>1,'AccountNumber'=>$account_field->js()->val(),'amount'=>$amount_field->js()->val())));

		$account_to_credit_model = $this->add('Model_Active_Account');
		$account_to_credit_model->addCondition('scheme_name','<>',CASH_ACCOUNT_SCHEME);
		$form->addField('autocomplete/Basic','account_to_credit')->setFieldHint('sdfsd')->setModel($account_to_credit_model,'AccountNumber');

		$form->addField('Text','narration');
		$form->addSubmit('Withdrawl');

		if($_GET['account_selected']){
			$account = $this->add('Model_Account')->tryLoadBy('AccountNumber',$_GET['account_selected']);
			if($account->loaded()){
				$right_col->add('H3')->set(array('Signature For - '));
				$right_col->add('View')->set($_GET['account_selected']);
				$right_col->add('View')->setHtml('Account Mode: ' . $account['ModeOfOperation'] . ($account['ModeOfOperation'] == 'Joint'? '<font color=red> Check All Signatures </font>':''));
				// $img=$right_col->add('View')->setElement('img')->setAttr('src','../signatures/sig_'.$account->ref('member_id')->get('id').'.JPG');
				$img=$right_col->add('View')->setElement('img')->setAttr('src',$account['sig_image']);
				$img->js('mouseover',$img->js()->width('200%'));
				$img->js('mouseout',$img->js()->width('100%'));
				$account_field->other_field->set($_GET['account_selected']);
				$account_field->set($account->id);
			}
		}else{
			$right_col->add('View_Error')->set('Select Account for withdral');
		}


		$js=array(
				$right_col->js()->reload(array('account_selected'=>$account_field->js()->val())),
			);
		$account_field->other_field->js('change',$js);
		$account_field->js('change',$js);
		$plus_btn->js('click',$js);

		if($form->isSubmitted()){
			try{		
				$account_model_temp = $this->add('Model_Account')
											->loadBy('AccountNumber',$form['account']);

				if(!$account_model_temp->loaded())
					$form->displayError('amount','Oops');

				$type = $account_model_temp->ref('scheme_id')->get('SchemeType');
				if($type=='DDS') $type = $account_model_temp->ref('scheme_id')->get('type');
				
				$account_model = $this->add('Model_Account_'.$type);
				$account_model->loadBy('AccountNumber',$form['account']);

				if($account_model['LockingStatus']) $form->displayError('account','This account is locked, contact admin');

				try {
					$this->api->db->beginTransaction();
				    	$account_model->withdrawl($form['amount'],$form['narration'],$form['account_to_credit']?array(array($form['account_to_credit']=>$form['amount'])):array(),$form);
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