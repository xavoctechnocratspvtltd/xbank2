<?php

class page_stock_actions_return extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$party_field=$form->addField('dropdown','party')->setEmptyText('Please Select');
		$party_field->setModel('Stock_Party');	
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');	
		$form->addField('line','qty');
		$form->addField('line','rate');
		$form->addField('text','narration');
		$form->addSubmit('Purchase Return');

		$grid=$this->add('Grid');
		$purchase_return_transaction=$this->add('Model_Stock_Transaction');
		$purchase_return_transaction->addCondition('transaction_type','PurchaseReturn');
		$grid->setModel($purchase_return_transaction,array('item','party','branch','qty','rate','narration','created_at'));

		if($form->isSubmitted()){
			$party=$this->add('Model_Stock_Party');
			$party->load($form['party']);
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->purchaseReturn($party,$item,$form['qty'],$form['rate'],$form['narration']);
			$form->js()->reload(null,$grid->js()->reload())->execute();
		}
	}
}