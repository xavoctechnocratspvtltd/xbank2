<?php

class page_dashboard_opened extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Account Open Today List','icon'=>'flag'));

		$grid=$this->add('Grid');
		$accounts=$this->add('Model_Account');
		$accounts->addCondition('created_at',$this->api->today);
		$grid->setModel($accounts);
	}
}