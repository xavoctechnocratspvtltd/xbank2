<?php
class Model_Memorandum_Transaction extends Model_Table {
	var $table = "memorandum_transactions";

	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id')->display(['form'=>'autocomplete/Basic'])->defaultValue($this->app->current_staff->id)->system(true);
		$this->hasOne('Branch','branch_id')->display(['form'=>'autocomplete/Basic'])->defaultValue($this->app->current_branch->id)->system(true);
		
		// used for reference for memorandom transaction is done
		// $this->hasOne('Account','reference_id')->display(['form'=>'autocomplete/Basic']);

		$this->addField('name'); //it may be invoice no
		$this->addField('memorandum_type')->setValueList($this->getTransactionType())->mandatory(true); // visiting charge
		$this->addField('narration')->type('text');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);

		$this->hasMany('Memorandum_TransactionRow','memorandum_transaction_id');

		$this->addExpression('amount_cr')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('amount_cr');
		});
		$this->addExpression('amount_dr')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('amount_dr');
		});

		$this->addExpression('tax_amount')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('tax_amount');
		});

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function getTransactionType(){
		$type = [
			'Visiting Charge'=>'Visiting Charge',
			'Legal Charge'=>'Legal Charge',
			'Cheque Return Charge'=>'Cheque Return Charge',
			'File Cancel Charge'=>'File Cancel Charge',
			'Godown Charge'=>'Godown Charge',
			'Legal Expenses Receipt'=>'Legal Expenses Receipt',
			'Minimum Balance Charge Received on Saving'=>'Minimum Balance Charge Received on Saving',
			'Noc handling Charge'=>'Noc handling Charge',
			'Staff Stationary Charge Received'=>'Staff Stationary Charge Received',
			'Bike Auction Charge'=>'Bike Auction Charge',
			'Legal Notice Sent For Bike Auction'=>'Legal Notice Sent For Bike Auction',
			'Final Recovery Notice Sent'=>'Final Recovery Notice Sent',
			'Notice Sent After Cheque Return'=>'Notice Sent After Cheque Return',
			'Society Notice Sent'=>'Society Notice Sent'
		];

		return $type;
	}

	/*
		row_data = [0=>
				[
					account_id=>
					tax=>
					amount_cr=>
					amount_dr=>
				],
			]
	*/

	function createNewTransaction($name=null,$memorandum_type,$narration,$row_data=[]){
		if(!count($row_data)) throw new \Exception("must pass row values for memmorandum transaction");
		

		try{
			$this->api->db->beginTransaction();

			$this['name'] = $name?:0;
			$this['memorandum_type'] = $memorandum_type;
			$this['narration'] = $narration;
			$this['created_at'] = $this->app->now;
			$this->save();

			foreach ($row_data as $key => $value) {
				$model_row = $this->add('Model_Memorandum_TransactionRow');
				$model_row['memorandum_transaction_id'] = $this->id;
				$model_row['account_id'] = $value['account_id'];
				$model_row['amount_cr'] = $value['amount_cr']?:0;
				$model_row['amount_dr'] = $value['amount_dr']?:0;
				$model_row['tax'] = $value['tax'];
				$model_row['created_at'] = $this->app->now;
				$model_row->save();
			}

			$this->api->db->commit();
		}catch(\Exception $e){
			$this->api->db->rollback();
			throw $e;
		}

	}

}