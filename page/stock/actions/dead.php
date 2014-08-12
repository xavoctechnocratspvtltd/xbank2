<?php

class page_stock_actions_dead extends Page {
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

		$form->addSubmit('Save');

		$grid=$this->add('Grid');

		$openning_transaction=$this->add('Model_Stock_Transaction');
		$openning_transaction->addCondition('transaction_type','Dead');
		$grid->setModel($openning_transaction,array('branch','staff','agent','dealer','item','qty','created_at','narration'));

		if($form->isSubmitted()){
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$staff=$this->add('Model_Staff')->tryLoad($form['staff']);
			$agent=$this->add('Model_Agent')->tryLoad($form['agent']);
			$dealer=$this->add('Model_Dealer')->tryLoad($form['dealer']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->dead($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			$form->js(null,$grid->js()->reload())->reload()->execute();
		}

	}
}