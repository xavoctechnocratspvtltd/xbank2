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

	function getGSTData($from_date,$to_date){
		$all_gst = [
			'GST 18'=>[],		
			'GST 28'=>[],
			'GST 5'=>[],
			'GST 6'=>[]
		];
		$data_array = [];

		foreach ($all_gst as $gst_name => $value) {
			$percent = explode(" ", $gst_name)[1];

			$gst_array = [
					$this->api->currentBranch['Code'].SP.'SGST '.($percent/2).'%',
					$this->api->currentBranch['Code'].SP.'CGST '.($percent/2).'%',
					$this->api->currentBranch['Code'].SP.'IGST '.$percent.'%'
				];

			$model_tra = $this->add('Model_GST_Transaction',['gst_array'=>$gst_array]);
			$model_tra->addCondition('created_at','>=',$from_date);
			$model_tra->addCondition('created_at','<',$this->app->nextDate($to_date));
			$model_tra->addCondition('branch_id',$this->app->currentBranch->id);

			if(!$model_tra->count()->getOne()) continue;

			$all_transaction = $model_tra->getRows(['cr_sum','dr_sum','taxable_value','igst','sgst','cgst','tax_amount_sum']);
						
			// echo "<pre>";
			// print_r($all_transaction);
			// echo "</pre>";

			$temp = ['id'=>$gst_name,'taxable_value'=>0,'igst'=>0,'cgst'=>0,'sgst'=>0,'total_tax'=>0,'total_value'=>0];
			foreach ($all_transaction as $key => $value) {
				$temp = [
					'id'=>$gst_name,
					'taxable_value'=>($temp['taxable_value']+$value['taxable_value']),
					'igst'=>$temp['igst'],
					'cgst'=>($temp['cgst']+$value['cgst']),
					'sgst'=>($temp['sgst']+$value['sgst']),
					'total_tax'=>($temp['total_tax']+$value['tax_amount_sum']),
					'total_value'=>($temp['total_value']+$value['dr_sum'])
				];
			}

			if(count($temp) && $temp['taxable_value'] > 0) $data_array[] = $temp;
		}

		return $data_array;
	}

}