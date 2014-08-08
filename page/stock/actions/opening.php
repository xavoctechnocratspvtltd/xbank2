<?php

class page_stock_actions_opening extends Page {
	function init(){
		parent::init();
		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');

		$form->addField('line','qty');
		$form->addField('line','rate');
		$form->addField('text','narration');

		$form->addSubmit('Save');

		$grid=$this->add('Grid');

		$openning_transaction=$this->add('Model_Stock_Transaction');
		$openning_transaction->addCondition('transaction_type','Openning');
		$grid->setModel($openning_transaction,array('branch','item','qty','rate','created_at','narration'));

		if($form->isSubmitted()){
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->openning($item,$form['qty'],$form['rate'],$form['narration']);
			$form->js(null,$grid->js()->reload())->reload()->execute();
		}

	}
}