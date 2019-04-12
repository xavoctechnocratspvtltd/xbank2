<?php

class page_memorandum_report extends Page{
	public $title = "Report";

	function init(){
		parent::init();

		$model = $this->add('Model_Account');
		$model->setActualFields(['id','AccountNumber']);
		$model->setLimit(10);

		$gst_array = [
				$this->api->currentBranch['Code'].SP.'SGST 9%',
				$this->api->currentBranch['Code'].SP.'CGST 9%',
				$this->api->currentBranch['Code'].SP.'IGST 18%'
			];
		$sgst_model_id = $this->add('Model_Account')->loadBy('AccountNumber',$gst_array[0])->id;
		$cgst_model_id = $this->add('Model_Account')->loadBy('AccountNumber',$gst_array[1])->id;

		// $igst_model_id = $this->add('Model_Account')->loadBy('AccountNumber',$gst_array[2])->id;

		foreach ($gst_array as $key => $gst_name) {
				
		}

		$grid = $this->add('Grid');
		$grid->setModel($model,['id','AccountNumber']);


		$model_tra = $this->add('Model_TransactionRow');
		$model_tra->addCondition('account_id',[$sgst_model_id,$cgst_model_id]);
		$model_tra->setOrder('id','desc');
		$model_tra->setLimit(10);
		$grid = $this->add('Grid');
		$grid->setModel($model_tra,['account','scheme','balance_sheet','transaction_type']);
	}
}