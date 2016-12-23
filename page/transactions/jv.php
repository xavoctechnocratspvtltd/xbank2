<?php

class page_transactions_jv extends Page {
	public $title = 'JV Entry';

	public $rows=5;

	function init(){
		parent::init();
		$this->rename('a');

		$this->add('Controller_Acl');
		// Only self branch accounts

		$form = $this->add('Form');

		$cols = $form->add('Columns');
		$dr_col = $cols->addColumn(6);
		$cr_col = $cols->addColumn(6);

		$cr_col_cols=$cr_col->add('Columns');
		$cr_account_col = $cr_col_cols->addColumn(6);
		$cr_amount_col = $cr_col_cols->addColumn(6);

		$dr_col_cols=$dr_col->add('Columns');
		$dr_account_col = $dr_col_cols->addColumn(6);
		$dr_amount_col = $dr_col_cols->addColumn(6);


		$cr_account_col->add('H3')->set('Credit');
		$cr_amount_col->add('H3')->set('-');
		$dr_account_col->add('H3')->set('Debit');
		$dr_amount_col->add('H3')->set('-');

		$account_cr_model=$this->add('Model_Active_Account');
		$account_cr_model->add('Controller_Acl');
		// $account_cr_model->addCondition('branch_id',$this->api->currentBranch->id);
		// $account_cr_model->filter(array($account_cr_model->scheme_join->table_alias.'.SchemeGroup'=>array('%Branch & Divisions%')));

		$account_dr_model=$this->add('Model_Active_Account');
		$account_dr_model->add('Controller_Acl');
		// $account_dr_model->addCondition('branch_id',$this->api->currentBranch->id);
		// $account_dr_model->filter(array($account_dr_model->scheme_join->table_alias.'.SchemeGroup'=>array('%Branch & Divisions%')));

		$j = 1;

		for($i=1;$i<=$this->rows;$i++){
			// echo $i."--cr<br/>";
			$j = $j+$i*$i;

			$account = $form->addField('autocomplete/Basic','account_cr_'.$i);
			$account->other_field->setAttr('tabindex',$j);
			$account->setModel($account_cr_model,'AccountNumber');
			$account->setCaption(' ');
			$amount = $form->addField('line','amount_cr_'.$i,'')->setAttr('tabindex',$j+1);

			$account->js(true)->closest('div.atk-form-row')->appendTo($cr_account_col);
			$amount->js(true)->closest('div.atk-form-row')->appendTo($cr_amount_col);
		}

		$j = 0;
		for($i=1;$i<=$this->rows;$i++){
			$j = $j+$i*$i;

			$account = $form->addField('autocomplete/Basic','account_dr_'.$i);
			$account->other_field->setAttr('tabindex',$j);
			$account->setModel($account_dr_model,'AccountNumber');
			$amount = $form->addField('line','amount_dr_'.$i,'')->setAttr('tabindex',$j + 1);

			$account->setCaption(' ');
			$account->js(true)->closest('div.atk-form-row')->appendTo($dr_account_col);
			$amount->js(true)->closest('div.atk-form-row')->appendTo($dr_amount_col);
		}



		$bottom_cols = $form->add('Columns');
		$jv_type_col = $bottom_cols->addColumn(3);		
		$exec_btn = $bottom_cols->addColumn(6);
		
		$narration_field = $form->addField('Text','narration')->setAttr('tabindex',100);
		$narration_field->js(true)->closest('div.atk-form-row')->appendTo($jv_type_col);

		$jv_type_field = $form->addField('DropDown','jv_type');
		$transaction_model = $jv_type_field->setModel('TransactionType');
		$transaction_model->id_field='name';

		$jv_type_field->js(true)->closest('div.atk-form-row')->appendTo($jv_type_col);
		$form->addSubmit('Execute')->js(true)->closest('div.atk-actions')->appendTo($exec_btn);

		if($this->api->auth->model['AccessLevel'] < 80)
			$jv_type_field->js(true)->closest('div.atk-form-row')->hide();

		if($form->isSubmitted()){
			$this->validateJV($form);

			try {
				$this->api->db->beginTransaction();
				    $transaction = $this->add('Model_Transaction');
					$transaction->createNewTransaction($form['jv_type'], null,null, $form['narration'],null, array());
					
					for ($i=1; $i < $this->rows; $i++) {
						if($form['account_dr_'.$i])
							$transaction->addDebitAccount($form['account_dr_'.$i], $form['amount_dr_'.$i]);
					}
		
					for ($i=1; $i < $this->rows; $i++) {
						if($form['account_cr_'.$i])
							$transaction->addCreditAccount($form['account_cr_'.$i], $form['amount_cr_'.$i]);
					}
					
					$transaction->execute();					
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Entry Done')->execute();
		}else{
			if($form->hasElement('jv_type'))
				$form->getElement('jv_type')->set('Journal Voucher Entry'); // JV
		}

	}

	function validateJV($form){
		$cr_account_no=0;
		$cr_amount_sum=0;
		
		$dr_account_no=0;
		$dr_amount_sum=0;

		for ($i=1; $i < $this->rows; $i++) { 
			if($form['account_cr_'.$i]){
				if(!$form['amount_cr_'.$i])
					$form->displayError('amount_cr_'.$i,'Amount missing');
				$cr_account_no++;
				$cr_amount_sum += $form['amount_cr_'.$i]?:0;
			}
		}
		for ($i=1; $i < $this->rows; $i++) { 
			if($form['account_dr_'.$i]){
				if(!$form['amount_dr_'.$i])
					$form->displayError('amount_dr_'.$i,'Amount missing');
				$dr_account_no++;
				$dr_amount_sum += $form['amount_dr_'.$i]?:0;
			}
		}

		if(abs($cr_amount_sum - $dr_amount_sum) > 0.01)
			$form->js()->univ()->errorMessage('Amount Not Same')->execute();

		if($cr_account_no == 0 or $dr_account_no == 0)
			$form->js()->univ()->errorMessage('Debit or Credit Account not Present')->execute();

		if($cr_account_no > 1 and $dr_account_no > 1)
			$form->js()->univ()->errorMessage('Debit or Credit Account Both must not be more then one')->execute();

	}
}