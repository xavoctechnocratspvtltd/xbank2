<?php

class Model_GST_Transaction extends Model_Transaction {
	public $gst_array=[];

	function init(){
		parent::init();

		if(!count($this->gst_array)){
			$this->gst_array = [
					$this->api->currentBranch['Code'].SP.'SGST 9%',
					$this->api->currentBranch['Code'].SP.'CGST 9%',
					$this->api->currentBranch['Code'].SP.'IGST 18%'
				];
		}

		$this->sgst_id = $sgst_model_id = $this->add('Model_Account')->addCondition('AccountNumber',$this->gst_array[0])->tryLoadAny()->id;
		$this->cgst_id = $cgst_model_id = $this->add('Model_Account')->addCondition('AccountNumber',$this->gst_array[1])->tryLoadAny()->id;
		$this->igst_id = $igst_model_id = $this->add('Model_Account')->addCondition('AccountNumber',$this->gst_array[2])->tryLoadAny()->id;

		$this->addExpression('tax_amount_sum')->set(function($m,$q){
			return $m->refSQL('TransactionRow')
					->addCondition('account_id',[$this->sgst_id,$this->cgst_id])
					->sum('amountDr');
		});

		$this->addExpression('taxable_value')->set(function($m,$q){
			return $q->expr('([0]-[1])',[$m->getElement('cr_sum'),$m->getElement('tax_amount_sum')]);
		});

		$this->addExpression('sgst')->set(function($m,$q){
			return $m->refSQL('TransactionRow')->addCondition('account_id',$this->sgst_id)->sum('amountDr');
		});

		$this->addExpression('cgst')->set(function($m,$q){
			return $m->refSQL('TransactionRow')->addCondition('account_id',$this->cgst_id)->sum('amountDr');
		});
		$this->addExpression('igst')->set(function($m,$q){
			if(!$this->igst_id) return "'0'";
			return $m->refSQL('TransactionRow')->addCondition('account_id',$this->igst_id)->sum('amountDr');
		});

		$this->addCondition('tax_amount_sum','>',0);
	}
}