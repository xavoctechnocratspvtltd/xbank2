<?php

class page_stock_actions_transfer extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$branch_field=$form->addField('dropdown','branch')->setEmptyText('Please Select');
		$branch_model=$this->add('Model_Branch');
		$branch_model->addCondition('id','<>',$this->api->currentBranch->id);
		$branch_field->setModel($branch_model);	
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');	
		$form->addField('line','qty');
		$form->addField('text','narration');
		$form->addSubmit('Transfer');

		$grid=$this->add('Grid');
		$transfer_transaction=$this->add('Model_Stock_Transaction');
		$transfer_transaction->addCondition('transaction_type','Transfer');
		$grid->setModel($transfer_transaction,array('item','to_branch','qty','rate','created_at','narration'));

		if($form->isSubmitted()){
			$branch=$this->add('Model_Branch');
			$branch->load($form['branch']);
			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);
			$transaction=$this->add('Model_Stock_Transaction');
			$transaction->transfer($item,$branch,$form['qty'],$form['narration']);
			$form->js()->reload(null,$grid->js()->reload())->execute();
		}
	}


}