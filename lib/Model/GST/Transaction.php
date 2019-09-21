<?php

class Model_GST_Transaction extends Model_Transaction {
	public $gst_array=[];

	public $tax_field = "amountDr";
	public $gst_report = "inward";
	//note gst_report == inward means purchase entry
	// ouward means sale-invoice

	function init(){
		parent::init();

		if(!count($this->gst_array)){
			$this->gst_array = [
					'sgst'=>$this->api->currentBranch['Code'].SP.'SGST 9%',
					'cgst'=>$this->api->currentBranch['Code'].SP.'CGST 9%',
					'igst'=>$this->api->currentBranch['Code'].SP.'IGST 18%'
				];
		}

		if(isset($this->gst_array['sgst']))
			$this->sgst_id = $this->add('Model_Account')->addCondition('AccountNumber',$this->gst_array['sgst'])->tryLoadAny()->id;
		
		if(isset($this->gst_array['cgst']))
			$this->cgst_id = $this->add('Model_Account')->addCondition('AccountNumber',$this->gst_array['cgst'])->tryLoadAny()->id;

		if(isset($this->gst_array['igst']))
			$this->igst_id = $this->add('Model_Account')->addCondition('AccountNumber',$this->gst_array['igst'])->tryLoadAny()->id;
		
		$this->addExpression('sgst')->set(function($m,$q){
			if(!$this->sgst_id) return "'0'";
			return $m->refSQL('TransactionRow')->addCondition('account_id',$this->sgst_id)->sum($this->tax_field);
		});

		$this->addExpression('cgst')->set(function($m,$q){
			if(!$this->cgst_id) return "'0'";
			return $m->refSQL('TransactionRow')->addCondition('account_id',$this->cgst_id)->sum($this->tax_field);
		});
		
		$this->addExpression('igst')->set(function($m,$q){
			if(!$this->igst_id) return "'0'";
			return $m->refSQL('TransactionRow')->addCondition('account_id',$this->igst_id)->sum($this->tax_field);
		});

		$this->addExpression('tax_amount_sum')->set(function($m,$q){
			return $q->expr('(IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0))',[
					$m->getElement('sgst'),
					$m->getElement('cgst'),
					$m->getElement('igst')
				]);
		});

		$this->addExpression('taxable_value')->set(function($m,$q){
			return $q->expr('([0]-[1])',[$m->getElement('cr_sum'),$m->getElement('tax_amount_sum')]);
		});

		// $this->addExpression('taxable_value')->set(function ($m, $q) {
		// 	return $m->refSQL('TransactionRow')->withScheme()
		// 		->addCondition('account_id','<>',[$this->sgst_id,$this->cgst_id,$this->igst_id])
		// 		->addCondition('SchemeType','Default')
		// 		->sum('amountCr');
		// });
			//}

		$this->addCondition('tax_amount_sum','>',0);
	}

	// return supplier/Purchase GST Data
	function getInwardGSTData($from_date,$to_date){
		return $this->getGSTData($from_date,$to_date,[TRA_PURCHASE_ENTRY],'in','inward');
	}

	// return sales GST Data
	function getOutWardGSTData($from_date,$to_date){
		return $this->getGSTData($from_date,$to_date,[TRA_PURCHASE_ENTRY],'notin','outward');
	}

	function getGSTData($from_date,$to_date,$transaction_type=[],$tra_operator="in",$gst_report="inward"){
		$all_gst = GST_VALUES;
		$data_array = [];

		foreach ($all_gst as $gst_name => $value) {
			$tax_array = explode(" ", $gst_name);
			$percent = $tax_array[1];

			if($tax_array[0] == "IGST") continue;

			$gst_array = [
					'sgst'=>$this->api->currentBranch['Code'].SP.'SGST '.($percent/2).'%',
					'cgst'=>$this->api->currentBranch['Code'].SP.'CGST '.($percent/2).'%',
					'igst'=>$this->api->currentBranch['Code'].SP.'IGST '.$percent.'%'
				];

			$tax_field = 'amountDr';
			if($gst_report == "outward") $tax_field = 'amountCr';
			$model_tra = $this->add('Model_GST_Transaction',['gst_array'=>$gst_array,'gst_report'=>$gst_report,'tax_field'=>$tax_field]);
			$model_tra->addCondition('created_at','>=',$from_date);
			$model_tra->addCondition('created_at','<',$this->app->nextDate($to_date));
			$model_tra->addCondition('branch_id',$this->app->currentBranch->id);
			if($gst_report == "outward") $model_tra->addCondition('is_sale_invoice',1);

			if(count($transaction_type) && $tra_operator=="in") $model_tra->addCondition('transaction_type',$transaction_type);
			if(count($transaction_type) && $tra_operator=="notin") $model_tra->addCondition('transaction_type','<>',$transaction_type);
			if(!$model_tra->count()->getOne()) continue;

			$all_transaction = $model_tra->getRows(['cr_sum','dr_sum','taxable_value','igst','sgst','cgst','tax_amount_sum']);
						
			// echo "<pre>";
			// print_r($all_transaction);
			// echo "</pre>";

			$temp = ['id'=>$gst_name,'taxable_value'=>0,'igst'=>0,'cgst'=>0,'sgst'=>0,'total_tax'=>0,'total_value'=>0];
			foreach ($all_transaction as $key => $value) {
				$temp = [
					'id'=>$gst_name,
					'taxable_value'=>($temp['taxable_value']+ ($value['cr_sum'] - ($value['igst']+$value['cgst']+$value['sgst']) ) ),
					'igst'=>($temp['igst']+$value['igst']),
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