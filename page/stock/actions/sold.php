<?php

class page_stock_actions_sold extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');

		$form->addField('line','qty');
		$form->addField('line','rate');
		$form->addField('line','amount');
		$form->addField('text','narration');
		$form->addSubmit('Sold');

		$grid=$this->add('Grid');
		$submit_transaction=$this->add('Model_Stock_Transaction');
		$submit_transaction->addCondition('transaction_type','Sold');
		$grid->setModel($submit_transaction,array('branch','item','qty','rate','amount','created_at'));

		if($form->isSubmitted()){
			$item=$this->add('Model_Stock_Item')->load($form['item']);
			// throw new Exception($item->getDeadQty($form['qty']));
			
			if(!$item->getDeadQty($form['qty']))
				$form->displayError('qty',"dead Item is not  in Such qty");
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->sold($item,$form['qty'],$form['rate'],$form['narration'],$form['amount']);
			$form->js(null,$grid->js()->reload())->reload()->execute();

		}

	}
}