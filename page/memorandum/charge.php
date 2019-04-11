<?php

class page_memorandum_charge extends Page {
	public $title='Memorandum Charge Applied';

	function init(){
		parent::init();

		$model_memo_tran = $this->add('Model_Memorandum_Transaction');

		$col = $this->add('Columns');
		$col1 = $col->addColumn(6);

		$form = $col1->add('Form');
		$form->addField('DropDown','transaction_type')
			->setValueList($model_memo_tran->getTransactionType())
			->setEmptyText('Please Select ...')
			->validateNotNull();

		$model_account = $this->add('Model_Account')->addCondition('branch_id',$this->app->current_branch->id);
		$form->addField('autocomplete/Basic','amount_from_account')->validateNotNull()->setModel($model_account);
		$form->addField('DropDown','tax')->setValueList(GST_VALUES)->validateNotNull();
		$form->addField('amount')->validateNotNull();
		$form->addField('text','narration');
		$form->addSubmit('Submit');

		$form->add('misc\Controller_FormAsterisk');

		if($form->isSubmitted()){
			$row_data = $this->getRowData($form->get());
			
			$model_memo_tran->createNewTransaction(null,$form['transaction_type'],$form['narration'],$row_data);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Saved Successfully')->execute();
		}
	}


	function getRowData($form){

		$tra_array = MEMORANDUM_ACCOUNT_TRA_ARRAY;
		$row_data = [];

		// amount_from_account may be account is customer/member or cash or bank account in cash of last tree transaction from constant MEMORANDUM_ACCOUNT_TRA_ARRAY
		// dr entry transaction row
		$total_cr_amount = 0;
		$total_dr_amount = $amount = $form['amount'];

		// dr ROW Entry
		$row_data[] = [
				'account_id'=>$form['amount_from_account'],
				'amount_dr'=>$form['amount'],
				'amount_cr'=>0,
				'tax'=>$form['tax']
			];

		//CR Transaction Row Calculation
		// GST Amount Calculation
		$tax = $form['tax'];
		$temp = explode(" ", $tax);
		$tax_name = $temp[0];
		$tax_percentage = $temp[1];
		$tax = (100 + $tax_percentage);
		$tax_excluded_amount = round((($amount/$tax)*100),2);
		$tax_amount = round(($amount - $tax_excluded_amount),2);
		
		if($tax_excluded_amount){
			// loading charge account model
			$account_number = $this->api->currentBranch['Code'].SP.$tra_array[$form['transaction_type']][0];
			$cr_charge_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$account_number);
			$cr_charge_account_model->tryLoadAny();
			if(!$cr_charge_account_model->loaded()) throw new \Exception("Account Not Found ".$account_number);

			$row_data[] = [
					'account_id'=>$cr_charge_account_model->id,
					'amount_cr'=>$tax_excluded_amount,
					'amount_dr'=>0,
					'tax'=>null
				];
			$total_cr_amount += $tax_excluded_amount;
		}
		

		if($tax_name === "GST"){
			$sgst_account_number = $this->api->currentBranch['Code'].SP."SGST ".round(($tax_percentage/2),1)."%";
			$cgst_account_number = $this->api->currentBranch['Code'].SP."CGST ".round(($tax_percentage/2),1)."%";

			$sgst_amount = $cgst_amount = ($tax_amount/2);

			$cr_gst_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$sgst_account_number);
			$cr_gst_account_model->tryLoadAny();
			if(!$cr_gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$sgst_account_number." )");
			$row_data[] = [
				'account_id'=>$cr_gst_account_model->id,
				'amount_cr'=>$sgst_amount,
				'amount_dr'=>0,
				'tax'=>null
			];

			$cr_gst_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$cgst_account_number);
			$cr_gst_account_model->tryLoadAny();
			if(!$cr_gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$cgst_account_number." )");
			$row_data[] = [
				'account_id'=>$cr_gst_account_model->id,
				'amount_cr'=>$cgst_amount,
				'amount_dr'=>0,
				'tax'=>null
			];

			$total_cr_amount += $sgst_amount;
			$total_cr_amount += $cgst_amount;
		}
		if($tax_name === "IGST"){
			$igst_account_number = $this->api->currentBranch['Code'].SP.$tax."%";
			
			$cr_gst_account_model = $this->add('Model_Account')->addCondition('AccountNumber',$igst_account_number);
			$cr_gst_account_model->tryLoadAny();
			if(!$cr_gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$igst_account_number." )");
			$row_data[] = [
				'account_id'=>$cr_gst_account_model->id,
				'amount_cr'=>$tax_amount,
				'amount_dr'=>0,
				'tax'=>null
			];

			$total_cr_amount += $tax_amount;

		}
		// end of gst calculation

		// check here validation sum of amount_cr === amount_dr
		if($total_cr_amount != $total_dr_amount) throw $this->exception('CR AND DR Amount Must Be Same ')
					->addMoreInfo('CR Amount',$total_cr_amount)
					->addMoreInfo('DR Amount',$total_dr_amount);

		return $row_data;
	}

}