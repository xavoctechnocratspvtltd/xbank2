<?php

class page_vcor extends Page {
	function init(){
		parent::init();

		$tra = $this->add('Model_Transaction');
		$tra->addCondition('branch_id',$this->api->currentBranch->id);
		$tra->addCondition('created_at','>=','2014-04-01');

		$grid= $this->add('Grid_AccountsBase');
		$grid->setModel($tra);

		$grid->addPaginator(200);

	}
}