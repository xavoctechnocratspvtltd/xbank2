<?php

class page_stock_actions_issue extends Page {
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
		$issue_transaction=$this->add('Model_Stock_Transaction');
		$issue_transaction->addCondition('transaction_type',array('Issue','Consume'));
		$grid->setModel($issue_transaction,array('branch','item','staff','qty','issue_date','narration','transaction_type'));

		if($form->isSubmitted()){
			$item=$this->add('Model_Stock_Item')->load($form['item']);
			$staff=$this->add('Model_Staff')->tryLoad($form['staff']);
			$agent=$this->add('Model_Agent')->tryLoad($form['agent']);
			$dealer=$this->add('Model_Dealer')->tryLoad($form['dealer']);
			$transaction=$this->add('Model_Stock_Transaction');

			if($item['is_consumable'])
					$transaction->consume($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			if($item['is_issueable'])
					$transaction->issue($item,$form['qty'],$form['narration'],$staff,$agent,$dealer);
			$form->js(null,$grid->js()->reload())->reload()->execute();

		}
	}
}