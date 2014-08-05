<?php

class page_stock_actions_purchase extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$party_field=$form->addField('dropdown','party')->setEmptyText('Please Select');
		$party_field->setModel('Stock_Party');	
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');	
		$form->addField('line','qty');
		$form->addField('line','rate');
		$form->addSubmit('Purchase');

		$grid=$this->add('Grid');
		$purchase_transaction=$this->add('Model_Stock_Transaction');
		$purchase_transaction->addCondition('transaction_type','Purchase');
		$grid->setModel($purchase_transaction,array('item','party','branch','qty','rate','created_at'));

		if($form->isSubmitted()){
			$party=$this->add('Model_Stock_Party');
			$party->load($form['party']);
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->purchase($party,$item,$form['qty'],$form['rate']);
			$form->js()->reload(null,$grid->js()->reload())->execute();
		}
	}


}