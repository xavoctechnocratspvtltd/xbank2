<?php

class page_memorandum_charge extends Page {
	public $title='Memorandum Charge Applied';

	function init(){
		parent::init();
		
		$this->add('Controller_Acl',['default_view'=>false]);

		$model_memo_tran = $this->add('Model_Memorandum_Transaction');

		$col = $this->add('Columns');
		$col1 = $col->addColumn(6);

		$form = $col1->add('Form');
		$form->addField('DropDown','transaction_type')
			->setValueList($model_memo_tran->getTransactionType())
			->setEmptyText('Please Select ...')
			->validateNotNull();

		$model_account = $this->add('Model_Active_Account')
						->addCondition('branch_id',$this->app->current_branch->id);

		$form->addField('autocomplete/Basic','amount_from_account')->validateNotNull()->setModel($model_account);
		$form->addField('DropDown','tax')->setValueList(['GST 18'=>'GST 18%'])->validateNotNull();
		$form->addField('amount')->validateNotNull();
		$form->addField('text','narration');
		$form->addSubmit('Submit');

		$form->add('misc\Controller_FormAsterisk');

		if($form->isSubmitted()){

			// first check charges is applicable or not
			$account_dr = $this->add('Model_Active_Account')->load($form['amount_from_account']);
			
			if($form['transaction_type'] == "visit_charge"){
				$last_visit_in_same_month = (date('m',strtotime($account_dr['visit_done_on'])) == date('m',strtotime($this->app->now)));
				$last_visit_within_last_15_days = ($this->app->my_date_diff($account_dr['visit_done_on'],$this->app->today)['days_total'] < 15);
				if($last_visit_in_same_month && $last_visit_within_last_15_days){
					$form->displayError('transaction_type','Visit must not be within 15 days in same month');
				}
			}

			$narration = "Being ".$form['transaction_type']." Debited in ".$account_dr['name']." ".$form['narration'];
			$row_data = $this->getRowData($form->get());
			
			$model_memo_tran->createNewTransaction(null,$form['transaction_type'],$narration,$row_data);

			$new_old_related_trantype = [
										'godowncharge_debited'=>'godowncharge_debited',
										'legal_notice_sent_for_bike_auction'=>'legal_notice_sent_for_bike_auction',
										'final_recovery_notice_sent'=>'final_recovery_notice_sent',
										'cheque_returned'=>'cheque_returned',
										'notice_sent_after_cheque_returned'=>'notice_sent_after_cheque_returned',
										'society_notice_sent'=>'society_notice_sent',
										'noc_handling_charge_received'=>'noc_handling_charge_received',
										'legal_notice_sent'=>'legal_notice_sent',
										'visit_charge'=>'visit_done',
										'legal_expenses_received'=>'in_legal'
									];

			// saving date and checkbox related data on actual account model
			$type = $new_old_related_trantype[$form['transaction_type']];
			$account_dr['is_'.$type] = true;

			if($type == 'in_legal'){
				$account_dr['legal_filing_date'] = $this->app->now;
			}else 
				$account_dr[$type.'_on'] = $this->app->now;

			$account_dr->save();

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

		$sgst_amount = round(($tax_amount/2),2);
		$cgst_amount = round(($tax_amount/2),2);
		if($tax_name === "GST"){
			$tax_excluded_amount = round(($amount - ($sgst_amount + $cgst_amount)),2);
		}

		if($tax_excluded_amount){
			// loading charge account model
			$account_number = $this->api->currentBranch['Code'].SP.$tra_array[$form['transaction_type']][0];
			$cr_charge_account_model = $this->add('Model_Active_Account')->addCondition('AccountNumber',$account_number);
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

			$cr_gst_account_model = $this->add('Model_Active_Account')->addCondition('AccountNumber',$sgst_account_number);
			$cr_gst_account_model->tryLoadAny();
			if(!$cr_gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$sgst_account_number." )");
			$row_data[] = [
				'account_id'=>$cr_gst_account_model->id,
				'amount_cr'=>$sgst_amount,
				'amount_dr'=>0,
				'tax'=>null
			];

			$cr_gst_account_model = $this->add('Model_Active_Account')->addCondition('AccountNumber',$cgst_account_number);
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
			
			$cr_gst_account_model = $this->add('Model_Active_Account')->addCondition('AccountNumber',$igst_account_number);
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

		$total_cr_amount = round($total_cr_amount,2);
		$total_dr_amount = round($total_dr_amount,2);
		// end of gst calculation

		// check here validation sum of amount_cr === amount_dr
		if($total_cr_amount != $total_dr_amount) throw $this->exception('CR AND DR Amount Must Be Same ')
					->addMoreInfo('Tax Excluded Amount',$tax_excluded_amount)
					->addMoreInfo('SGST',$sgst_amount)
					->addMoreInfo('CGST',$cgst_amount)
					->addMoreInfo('Total Amount',$amount)
					->addMoreInfo('Total CR Amount',$total_cr_amount)
					->addMoreInfo('Total DR Amount',$total_dr_amount)
					->addMoreInfo('Difference',($total_dr_amount - $total_cr_amount));

		return $row_data;
	}

}