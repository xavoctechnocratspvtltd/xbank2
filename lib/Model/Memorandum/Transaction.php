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
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);

		$this->hasMany('Memorandum_TransactionRow','memorandum_transaction_id');

		$this->addExpression('amountCr')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('amountCr');
		});
		$this->addExpression('amountDr')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('amountDr');
		});

		$this->addExpression('tax_amount')->set(function($m,$q){
			return $m->refSQL('Memorandum_TransactionRow')->sum('tax_amount');
		});

		$this->addHook('beforeDelete',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		$this->ref('Memorandum_TransactionRow')->deleteAll();
	}

	function getTransactionType(){
		$type = MEMORANDUM_TRA_ARRAY;
		// unset($type['insurance_processing_fees']);
		// unset($type['file_cancel_charge']);
		// unset($type['staff_stationary_charge_received']);
		
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
		if(!count($row_data)) throw new \Exception("must pass row values for memorandum transaction");
		

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
				$model_row['amountCr'] = $value['amount_cr']?:0;
				$model_row['amountDr'] = $value['amount_dr']?:0;
				$model_row['created_at'] = $this->app->now;
				$model_row['tax'] = $value['tax']?:0;
				$model_row->save();
			}

			$this->api->db->commit();
		}catch(\Exception $e){
			$this->api->db->rollback();
			throw $e;
		}

	}

}