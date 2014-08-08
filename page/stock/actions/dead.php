<?php

class page_stock_actions_dead extends Page {
	function init(){
		parent::init();
		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');

		$form->addField('line','qty');
		$form->addField('text','narration');

		$form->addSubmit('Save');

		$grid=$this->add('Grid');

		$openning_transaction=$this->add('Model_Stock_Transaction');
		$openning_transaction->addCondition('transaction_type','Dead');
		$grid->setModel($openning_transaction,array('branch','item','qty','created_at','narration'));

		if($form->isSubmitted()){
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->dead($item,$form['qty'],$form['narration']);
			$form->js(null,$grid->js()->reload())->reload()->execute();
		}

	}
}