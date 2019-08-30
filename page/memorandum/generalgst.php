<?php

class page_memorandum_generalgst extends Page{
	public $title = "General GST Voucher";

	public $transaction_type=null;
	public $dr_account=null;
	public $cr_account=null;
	public $sgst_account_model=null;
	public $cgst_account_model=null;
	public $dr_model=0;
	public $cr_model=0;
	public $cr_amount=0;
	public $total_tax_amount=0;
	public $sgst_tax_amount=0;
	public $cgst_tax_amount=0;

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$col = $this->add('Columns');
		$col1 = $col->addColumn(6);

		$account_model = $this->add('Model_Active_Account')->addCondition('branch_id',$this->app->current_branch->id);

		$form = $col1->add('Form');
		$form->addField('autocomplete/Basic','dr_account')->validateNotNull()->setModel($account_model);
		$cr_account_model = $this->add('Model_Active_Account')->addCondition('branch_id',$this->app->current_branch->id);
		$cr_account_model->addCondition('scheme_id','in',[14,10]);				
		$form->addField('autocomplete/Basic','cr_account')->validateNotNull()->setModel($cr_account_model);

		$form->addField('amount_included_gst')->validateNotNull();
		$form->addField('DropDown','tax')->setValueList(['GST 18'=>'GST 18%'])->validateNotNull();
		$form->addField('Text','narration');
		$form->addSubmit('Submit');

		$form->add('misc\Controller_FormAsterisk');

		if($form->isSubmitted()){

			$this->setTransactionData($form->get());

			try{
				$this->api->db->beginTransaction();

					$narration = $form['narration'];
					$transaction = $this->add('Model_Transaction');
					$invoice_no = $transaction->newInvoiceNumber($this->app->now);
										
					$transaction->createNewTransaction('General GST',$this->api->currentBranch,$this->app->now,$narration,null,['reference_id'=>$form['dr_account'],'invoice_no'=>$invoice_no]);
					//amount from account credit
					$transaction->addDebitAccount($this->dr_model,$form['amount_included_gst']);
					//charge ie(visit, etc), gst are debit
					$transaction->addCreditAccount($this->cr_model,$this->cr_amount);
					$transaction->addCreditAccount($this->sgst_account_model,$this->sgst_tax_amount);
					$transaction->addCreditAccount($this->cgst_account_model,$this->cgst_tax_amount);
					$transaction->execute();
					
				$this->api->db->commit();
			}catch(\Exception $e){
				$this->api->db->rollback();
				throw $e;
			}

			// $model_memo_tran->createNewTransaction(null,$form['transaction_type'],$form['narration'],$row_data);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Saved Successfully')->execute();
		}

	}


	function setTransactionData($form){

		$tax_percentage = 18;
		$tax = (100 + $tax_percentage);
		$tax_excluded_amount = round((($form['amount_included_gst']/$tax)*100),2);
		$this->total_tax_amount = $tax_amount = round(($form['amount_included_gst'] - $tax_excluded_amount),2);

		$sgst_account_number = $this->api->currentBranch['Code'].SP."SGST 9%";
		$cgst_account_number = $this->api->currentBranch['Code'].SP."CGST 9%";

		$this->sgst_account_model = $gst_account_model = $this->add('Model_Active_Account')->addCondition('AccountNumber',$sgst_account_number);
		$gst_account_model->tryLoadAny();
		if(!$gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$sgst_account_number." )");

		$this->cgst_account_model = $gst_account_model = $this->add('Model_Active_Account')->addCondition('AccountNumber',$cgst_account_number);
		$gst_account_model->tryLoadAny();
		if(!$gst_account_model->loaded()) throw new \Exception("GST Account Not found ( ".$cgst_account_number." )");

		$this->sgst_tax_amount = $this->cgst_tax_amount = round(($tax_amount/2),2);

		$this->cr_amount =  round($form['amount_included_gst'] - ($this->sgst_tax_amount + $this->cgst_tax_amount),2);
		
		$this->dr_model = $this->add('Model_Active_Account')->load($form['dr_account']);
		$this->cr_model = $this->add('Model_Active_Account')->load($form['cr_account']);

	}

}