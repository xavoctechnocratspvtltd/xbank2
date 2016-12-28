<?php

class page_transactions_deposit extends Page {
	public $title ='Deposit Amount';
	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$cols= $this->add('Columns');
		$left_col = $cols->addColumn(6);
		$right_col = $cols->addColumn(6);
		$form = $left_col->add('Form');
		$account_model=$this->add('Model_Active_Account');

		// NO BANK FOR ANY BRANCH
		$account_model->addCondition('scheme_name','<>',BANK_ACCOUNTS_SCHEME);
		$account_model->addCondition('scheme_name','<>',BANK_OD_SCHEME);
		
		// NO CASH FOR ANY BRANCH
		$account_model->addCondition('scheme_name','<>',CASH_ACCOUNT_SCHEME);
		
		// NO DEFAULT ACCOUNTS FOR OTHER BRANCH
		$account_model->addCondition(
			$account_model->dsql()->orExpr()
				->where('branch_id',$this->api->current_branch->id)
				->where('DefaultAC',false)
			);
		
		// No Fixed and Mis Accounts For Any Branch
		$account_model->addCondition('SchemeType','<>',ACCOUNT_TYPE_FIXED);

		// IF MEMBER OF ACCOUNT IS NOT HAVING PAN CARD .. DISPLAY MESSAGE IF AMOUNT IS >= 50,000
		$account_field = $form->addField('autocomplete/Basic',array('name'=>'account'))->validateNotNull();
		// $account_field->other_field->js(true)->focus();
		$plus_btn=$account_field->other_field->afterField()->add('Button')->set(array('icon'=>'plus',''));
		// $account_model->filter(array($account_model->scheme_join->table_alias.'.SchemeGroup'=>array('%Bank Accounts%','%Suspence Account%','%Cash Account%','%Branch & Divisions%'),$account_model->table_alias.'.account_type'=>array('%Saving%','%Current%')));
		$account_field->setModel($account_model,'AccountNumber');
		$account_mode_view = $account_field->belowField()->add('View');

		// $account_field->model->debug();
		$amount_field = $form->addField('Number','amount')->validateNotNull();
		$pan_details = $amount_field->belowField()->add('View');

		if($_GET['check_min']){
			$acc_bal_temp = $this->add('Model_Account_SavingAndCurrent');
			$acc_bal_temp->tryLoadBy('AccountNumber',$_GET['AccountNumber']);

			if($acc_bal_temp->loaded()){
				$bal = $acc_bal_temp->getOpeningBalance();
				$bal_cr = $bal['Cr'] - $bal['Dr'];
				if($bal_cr- $_GET['amount'] < 200 AND $bal_cr- $_GET['amount'] > 0 ) $pan_details->set('Bellow');
			}

		}
		$amount_field->js('change',$pan_details->js()->reload(array('check_min'=>1,'amount_filled'=>$amount_field->js()->val(),'account_selected'=>$account_field->js()->val())));

		$account_to_debit_model = $this->add('Model_Active_Account');
		$account_to_debit_model->addCondition('scheme_name','<>',CASH_ACCOUNT_SCHEME);
		$form->addField('autocomplete/Basic','account_to_debit')->setModel($account_to_debit_model,'AccountNumber');
		
		$form->addField('Text','narration');
		$form->addSubmit('Deposit');

		if($_GET['account_selected']){
			$account_selected =$this->add('Model_Account');
			$account_selected->loadBy('AccountNumber',$_GET['account_selected']);
			if($_GET['amount_filled'] >=50000 and (strlen($account_selected->ref('member_id')->get('PanNo')) != 10) ){
				$pan_details->setHTML('<font color="red">No Pan Card Found</font>');
			}elseif($_GET['amount_filled'] < 50000 and strlen($account_selected->ref('member_id')->get('PanNo')) != 10){
				$pan_details->set('Pan Card Not Found, But not needed');
			}else{
				$pan_details->set('Pan Card Found');
			}
			// return;
			if($account_selected->loaded()){
				// $right_col->add('H3')->set(array('Signature For - '));
				$right_col->add('View')->set('Scheme Type: '.$account_selected['SchemeType']);
				$account_info=$right_col->add('View');
				switch ($account_selected['SchemeType']) {
					case 'Loan':
						$account_info->set("Over Due Premiums ". $account_selected->ref('Premium')->addCondition('DueDate','<',$this->api->today)->addCondition('Paid',false)->count());
						$right_col->add('View')->set('Premium Amount ' . $account_selected['Amount']);
						break;
					case 'Recurring':
						$account_info->set("Paid Premiums ".$account_selected->ref('Premium')->addCondition('PaidOn','is not',null)->count());
						$right_col->add('View')->set('Premium Amount ' . $account_selected['Amount']);
						break;	
					default:
						$account_info->set($account_selected['scheme_name']);
						break;
				}
				// $right_col->add('View')->setHtml('Account Mode: ' . $account_selected['ModeOfOperation'] . ($account_selected['ModeOfOperation'] == 'Joint'? '<font color=red> Check All Signatures </font>':''));
				$account_field->other_field->set($_GET['account_selected']);
				$account_field->set($account_selected->id);
			}else{
				$right_col->add('View_Error')->set('Select Account for Deposit');
			}
		}

		$js=array(
				$right_col->js()->reload(array('account_selected'=>$account_field->js()->val())),
			);
		
		$plus_btn->js('click',$js);
		$account_field->other_field->js('change',$js);
		$account_field->js('change',$js);
		// removed cash from here as default account is taken to be self branch cash account

		if($form->isSubmitted()){
			
			$account_model_temp = $this->add('Model_Account')
										->loadBy('AccountNumber',$form['account']);

			if(!$account_model_temp->loaded())
				$form->displayError('amount','Oops');

			$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
			$account_model->loadBy('AccountNumber',$form['account']);
			

			try {
				$this->api->db->beginTransaction();
			    $account_model->deposit($form['amount'],$form['narration'],$form['account_to_debit']?array(array($form['account_to_debit']=>$form['amount'])):array(),$form);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$member=$account_model->ref('member_id');
			$msg="Dear Member, your account ".$account_model['AccountNumber']." has been credited with amount ".$form['amount'].".";
			
			$mobile_no=explode(',', $member['PhoneNos']);
			if(strlen(trim($mobile_no[0])) == 10){
				$sms=$this->add('Controller_Sms');
				$sms->sendMessage($mobile_no[0],$msg);
			}
					

			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- deposited in " . $form['account'])->execute();
		}
	}
}