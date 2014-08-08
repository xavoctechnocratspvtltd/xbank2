<?php

class page_stock_actions_submit extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');

		$form->addField('line','qty');
		$form->addField('text','narration');
		$form->addSubmit('Issue');

		$grid=$this->add('Grid');
		$submit_transaction=$this->add('Model_Stock_Transaction');
		$submit_transaction->addCondition('transaction_type','Submit');
		$grid->setModel($submit_transaction,array('branch','item','qty','submit_date'));

		if($form->isSubmitted()){
			$item=$this->add('Model_Stock_Item')->load($form['item']);
			if(!$item->canSubmit($form['qty']))
				$form->displayError('qty',"This Item is not issue in Such qty");
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->submit($item,$form['qty'],$form['narration']);
			$form->js(null,$grid->js()->reload())->reload()->execute();

		}

	}
}