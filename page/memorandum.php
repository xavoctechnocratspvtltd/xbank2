<?php

class page_memorandum extends Page {
	public $title='Memorandum';

	function init(){
		parent::init();

		$model_memo_tran = $this->add('Model_Memorandum_Transaction');

		$col = $this->add('Columns');
		$col1 = $col->addColumn(6);

		$form = $col1->add('Form');
		$form->addField('DropDown','transaction_type')
			->setValueList($model_memo_tran->getTransactionType())
			->setEmptyText('Please Select ...')
			->validateNotNull();

		$model_account = $this->add('Model_Account')->addCondition('branch_id',$this->app->current_branch->id);
		$form->addField('autocomplete/Basic','amount_from_account')->validateNotNull()->setModel($model_account);
		$form->addField('DropDown','tax')->setValueList(GST_VALUES)->validateNotNull();
		$form->addField('amount')->validateNotNull();
		$form->addField('text','narration');
		$form->addSubmit('Submit');

		$form->add('misc\Controller_FormAsterisk');

		if($form->isSubmitted()){
			$row_data = [0=>
				[
					'account_id'=>$form['amount_from_account'],
					'amount_cr'=>$form['amount'],
					'tax'=>$form['tax']
				]];
			$model_memo_tran->createNewTransaction(null,$form['transaction_type'],$form['narration'],$row_data);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Saved Successfully')->execute();
		}

		$grid = $this->add('Grid');
		$grid->setModel($model_memo_tran);

	}
}