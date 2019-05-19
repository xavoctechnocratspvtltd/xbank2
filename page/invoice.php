<?php

class page_invoice extends Page {
	public $title='All Invoice';

	function page_index(){
		// parent::init();
		$this->add('Controller_Acl',['default_view'=>false]);
		
		$model = $this->add('Model_Transaction');
		$model->addCondition('is_sale_invoice',true);
		$model->addCondition('transaction_type','<>','PURCHASE ENTRY');
		$model->getElement('created_at')->sortable(true);

		$crud = $this->add('CRUD',['allow_add'=>false,'allow_del'=>false]);
		$crud->setModel($model);

		$crud->addRef('TransactionRow',['label'=>'Detail']);
		$crud->grid->addPaginator(50);
		$crud->grid->addColumn('expander','invoicecancel');
	}

	function page_invoicecancel(){
		$id = $this->app->stickyGET('transactions_id');

		$form = $this->add('Form');
		$form->addField('Readonly','transaction_id')->set($id);
		$form->addSubmit('Are You Sure You Want to cancel the Invoice')->addClass('atk-swatch-red');

		if($form->isSubmitted()){
			$tr_model = $this->add('Model_Transaction')->load($form['transaction_id']);

			if($tr_model['is_invoice_cancel']){
				throw new \Exception("Invoice already Cancel");
			}
			
			try{
				$this->api->db->beginTransaction();

				$narration = "Being Invoice Cancel ";
				$transaction = $this->add('Model_Transaction');
				$transaction->createNewTransaction("Invoice Cancel",$tr_model->ref('branch_id')->load($tr_model['branch_id']),$this->app->now,$narration,null,['reference_id'=>$tr_model['reference_id']]);
				
				foreach ($tr_model->rows() as $model){
					if($model['side'] == "DR"){
						$cr_model = $this->add('Model_Account')->load($model['account_id']);
						$transaction->addCreditAccount($cr_model,$model['amountDr']);
					}else{
						$dr_model = $this->add('Model_Account')->load($model['account_id']);
						$transaction->addDebitAccount($dr_model,$model['amountCr']);
					}
				}

				$tr_model['is_invoice_cancel'] = true;
				$tr_model->save();
				$transaction->execute();
				$this->app->db->commit();
			}catch(\Exception $e){
				$this->api->db->rollback();
				throw $e;
			}

			$form->js(null,$form->js()->univ()->successMessage('Invoice Cancel Successfully'))->execute();
		}
	}

}