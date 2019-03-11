<?php

class page_mos extends Page {
	public $title='Marketing manager (MO/RO), TeleCallers Management';

	function page_index(){

		$tabs = $this->add('Tabs');
		$mo_tab = $tabs->addTab('Mo/RO List');
		$mo_change_tab = $tabs->addTabURL('moagentupdate','Mo Agent Update');
		$association_edit = $tabs->addTabURL('romoassociationedit','MO/RO Association Edit');
		$telecaller_list = $tabs->addTabURL('telecaller_manage','Telecaller Management');
		$telecaller_list = $tabs->addTabURL('telecaller_historyedit','Telecaller History Edit');

		// MO/RO add edit
		$crud = $mo_tab->add('CRUD',['entity_name'=>'Mo/RO']);
		$crud->setModel('Mo');
		$crud->add('Controller_Acl',['default_view'=>false]);
		
	}
}