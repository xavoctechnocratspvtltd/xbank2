<?php

class page_memorandum_report extends Page{
	public $title = "Report";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$tab = $this->add('Tabs');
		$tab->addTabURL('memorandum_report_gstr1','GSTR-1');
		$tab->addTabURL('memorandum_report_gstr2','GSTR-2');
	}
}