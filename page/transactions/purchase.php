<?php

class page_transactions_purchase extends Page {
	public $title ='Purchase Transaction';
	public $session_model;

	function init(){
		parent::init();
		
		$supplier_model = $this->add('Model_Supplier')->addCondition('is_active',true);
		$form = $this->add('Form');
		$field_supplier = $form->addField('DropDown','supplier')
				->setEmptyText('Please Select ...')->validateNotNull();
		$field_supplier->setModel($supplier_model);

		$this->session_model = $this->getSessionModel();

		$crud = $this->add('CRUD',['entity_name'=>'Purchase Item','allow_edit'=>false]);
		if($crud->isEditing('add') Or $crud->isEditing('edit')){
			$crud->form->add('misc\Controller_FormAsterisk');
		}
		$crud->setModel($this->session_model);
		$form->add('misc\Controller_FormAsterisk');

		if($form->isSubmitted()){

			if(!$this->session_model->count()) throw new \Exception("Please Add Purchase Item First ...");

			$data = [];
			foreach ($this->session_model as $record) {
				$data[] = $record->data;
			}
			
			$supplier_model = $this->add('Model_Supplier')->load($form['supplier']);

			try {
				$this->api->db->beginTransaction();
			    $supplier_model->createPurchaseTransaction($data);
			    $this->session_model->deleteAll();
			    $this->api->db->commit();

			} catch (Exception $e) {
			   	$this->api->db->rollBack();
			   	throw $e;
			}
			$form->js(null,$form->js()->reload())->univ()->successMessage($form['amount']."/- conveynace added in " . $form['amount_from_account'])->execute();
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

		$field_pur_acount = $session_model->addField('purchase_account');
		$field_pur_acount->display(['form'=>'autocomplete/Basic'])->mandatory(true);
		$field_pur_acount->setModel($model_default_account);

		$session_model->addField('tax_included_amount')->mandatory(true);
		$session_model->addField('tax')->mandatory(true);
		$session_model->addField('tax_excluded_amount');

		$session_model->addHook('afterLoad',function($m){$m['purchase_account'] = $this->add('Model_Account_Default')->load($m['purchase_account'])->get('name'); });
		$session_model->addHook('beforeSave',function($m){
			$tax = (100 + $m['tax']);
			$m['tax_excluded_amount'] = round((($m['tax_included_amount']/$tax)*100),2);
		});

		return $session_model;
	}
}