<?php

class page_stock_actions_submit extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select')->validateNotNull();
		$item_field->setModel('Stock_Item');

		$staff_field=$form->addField('dropdown','staff')->setEmptyText('Please Select');
		$staff_field->setModel('Staff');

		$agent_field=$form->addField('dropdown','agent')->setEmptyText('Please Select');
		$agent_field->setModel('Agent');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('Dealer');

		$form->addField('line','qty');
		$form->addField('text','narration');
		$form->addSubmit('Issue');

		$grid=$this->add('Grid');
		$submit_transaction=$this->add('Model_Stock_Transaction');
		$submit_transaction->addCondition('transaction_type','Submit');
		$grid->setModel($submit_transaction,array('branch','staff','agent','dealer','item','qty','submit_date','narration'));

		if($form->isSubmitted()){
			
			$staff=$this->add('Model_Staff')->tryLoad($form['staff']);
			$agent=$this->add('Model_Agent')->tryLoad($form['agent']);
			$dealer=$this->add('Model_Dealer')->tryLoad($form['dealer']);

			$item=$this->add('Model_Stock_Item')->load($form['item']);
			if(!$item->canSubmit($form['qty']))
				$form->displayError('qty',"This Item is not issue in Such qty");
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->submit($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			$form->js(null,$grid->js()->reload())->reload()->execute();
		}

	}
}