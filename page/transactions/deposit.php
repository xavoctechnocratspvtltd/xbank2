<?php

class page_transactions_deposit extends Page {
	public $title ='Deposit Amount';
	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$form = $this->add('Form');
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
		// $account_model->filter(array($account_model->scheme_join->table_alias.'.SchemeGroup'=>array('%Bank Accounts%','%Suspence Account%','%Cash Account%','%Branch & Divisions%'),$account_model->table_alias.'.account_type'=>array('%Saving%','%Current%')));
		$account_field->setModel($account_model,'AccountNumber');

		// $account_field->model->debug();

		$amount_field = $form->addField('Number','amount')->validateNotNull();
		$pan_details = $amount_field->belowField()->add('View');

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
			return;
		}

		$amount_field->js('change',$pan_details->js()->reload(array('amount_filled'=>$amount_field->js()->val(),'account_selected'=>$account_field->js()->val())));

		// removed cash from here as default account is taken to be self branch cash account
		$account_to_debit_model = $this->add('Model_Account');
		$account_to_debit_model->addCondition('scheme_name','<>',CASH_ACCOUNT_SCHEME);
		
		$form->addField('autocomplete/Basic','account_to_debit')->setModel($account_to_debit_model,'AccountNumber');
		$form->addField('Text','narration');
		$form->addSubmit('Deposit');

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
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- deposited in " . $form['account'])->execute();
		}
	}
}