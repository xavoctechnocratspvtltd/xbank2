<?php

class page_reports_member_defaulter extends Page{
	function init(){
		parent::init();

		$grid=$this->add('Grid');
		$member=$this->add('Model_Member');
		$member->addCondition('is_defaulter',true);
		$grid->setModel($member);
		$grid->addPaginator(50);
	}
}