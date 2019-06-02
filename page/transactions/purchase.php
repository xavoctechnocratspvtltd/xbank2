<?php

class page_transactions_purchase extends Page {
	public $title ='Purchase Transaction';
	public $session_model;

	function init(){
		parent::init();
			
		$this->add('Controller_Acl',['default_view'=>false]);
		// GST Account is Credted by Manual

		// $supplier_model = $this->add('Model_Supplier')->addCondition('is_active',true);
		$supplier_model = $this->add('Model_Account_Default')
					->addCondition('related_type','Model_Supplier')
					->addCondition('branch_id',$this->app->current_branch->id);

		$form = $this->add('Form',null,null,['form/horizontal']);
		$field_supplier = $form->addField('DropDown','supplier')
				->setEmptyText('Please Select ...')->validateNotNull();

		$field_supplier->setModel($supplier_model);

		$form->addField('invoice_no')->validateNotNull();
		$form->addField('Text','narration')->set('Purchase Entry');
		$form->addField('Number','tds_amount');

		$this->session_model = $this->getSessionModel();
		$crud = $this->add('CRUD',['entity_name'=>'Purchase Item','allow_edit'=>false]);
		if($crud->isEditing('add') Or $crud->isEditing('edit')){
			$crud->form->add('misc\Controller_FormAsterisk');
		}
		$crud->setModel($this->session_model,['purchase_account','tax_included_amount','tax'],['purchase_account','tax_excluded_amount','tax','tax_amount','tax_included_amount']);
		
		$form->add('misc\Controller_FormAsterisk');
		if($form->isSubmitted()){

			if(!$this->session_model->count()) throw new \Exception("Please Add Purchase Item First ...");

			$f_year = $this->api->getFinancialYear($this->app->now);
			$start_date = $f_year['start_date'];
			$end_date = $f_year['end_date'];

			$tr_model = $this->add('Model_Transaction')
						->addCondition('is_sale_invoice',0)
						->addCondition('invoice_no',$form['invoice_no'])
						->addCondition('created_at','>=',$start_date)
						->addCondition('created_at','<',$this->api->nextDate($end_date))
						->addCondition('reference_id',$form['supplier'])
						->tryLoadAny();
			if($tr_model->loaded()) $form->displayError('invoice_no','this purchase invoice is already used on '.$tr_model['created_at']);
			 

			$data = [];
			foreach ($this->session_model as $record) {
				$data[] = $record->data;
			}

			// $supplier_ac_model = $this->add('Model_Account_Default')->load($form['supplier']);
			$data = $this->getTransactionData($form['supplier'],$data);

			$data['narration'] = $form['narration'];
			$data['tds_amount'] = $form['tds_amount'];
			$data['invoice_no'] = $form['invoice_no'];

			try {
				$this->api->db->beginTransaction();
				$supplier_model = $this->add('Model_Supplier');
			    $supplier_model->createPurchaseTransaction($data);
			    $this->session_model->deleteAll();
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,[$form->js()->reload(),$crud->js()->reload()])->univ()->successMessage("successfully submitted")->execute();
		}

		$this->add('Button')
			->set('Submit')
			->setStyle('margin-top','10px;')
			->js('click',$form->js()->submit());

	}

	function getSessionModel(){
		$session_model = $this->add('Model',['table'=>'purchase']);
		$session_model->setSource('Session');
		
		$model_default_account = $this->add('Model_Account_Default');
		$model_default_account->addCondition('branch_id',$this->app->current_branch->id);

		$session_model->addField('purchase_account_id');
		$field_pur_acount = $session_model->addField('purchase_account');
		$field_pur_acount->display(['form'=>'autocomplete/Basic'])->mandatory(true);
		$field_pur_acount->setModel($model_default_account);


		$session_model->addField('tax_included_amount')->mandatory(true);
		$session_model->addField('tax')->setValueList(GST_VALUES);
		$session_model->addField('tax_amount');
		$session_model->addField('tax_excluded_amount');

		$session_model->addHook('afterLoad',function($m){
			$m['purchase_account_id'] = $m['purchase_account'];
			$m['purchase_account'] = $this->add('Model_Account_Default')->load($m['purchase_account'])->get('name');
		});
		$session_model->addHook('beforeSave',function($m){
			if($m['tax']){
				$temp = explode(" ", $m['tax']);
				$tax_name = $temp[0];
				$tax_percentage = $temp[1];
				$tax = (100 + $tax_percentage);
				$m['tax_excluded_amount'] = round((($m['tax_included_amount']/$tax)*100),2);
				$m['tax_amount'] = round(($m['tax_included_amount'] - $m['tax_excluded_amount']),2);
			}else{
				$m['tax_excluded_amount'] = $m['tax_included_amount'];
			}
		});

		return $session_model;
	}


	function getTransactionData($supplier_ac_id,$data){

		if(!$supplier_ac_id) throw new \Exception("Supplier Account Not Found");

		$tra_data = [
				'cr'=>['account_id'=>0,'amount'=>0],
				'dr'=>[
					'account'=>[],
					'gst'=>[]
					],
				'total_amount'=>0,
				'tds_amount'=>0,
				'invoice_no'=>0
			];

		$total_amount = 0;
		foreach ($data as $key => $value) {

			if(!isset($tra_data['dr']['account'][$value['purchase_account_id']])) $tra_data['dr']['account'][$value['purchase_account_id']] = 0;
			$tra_data['dr']['account'][$value['purchase_account_id']] += $value['tax_excluded_amount'];

			if($value['tax'] && !isset($tra_data['dr']['gst'][$value['tax']])) $tra_data['dr']['gst'][$value['tax']] = 0;
			$tra_data['dr']['gst'][$value['tax']] += $value['tax_amount'];

			$total_amount += $value['tax_included_amount'];
		}
		$tra_data['total_amount'] = $total_amount;
		$tra_data['cr']['account_id'] = $supplier_ac_id;
		$tra_data['cr']['amount'] = $total_amount;

		return $tra_data;
	}
}