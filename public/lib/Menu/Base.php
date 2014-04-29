<?php

class Menu_Base extends Menu {
	function init(){
		parent::init();

		$this->addMenuItem('index','Dashboard');
		$admin = $this->addMenuItem('index','Super Admin');


		$this->addMenuItem('members','Members');
		$this->addMenuItem('accounts','Accounts');
		$this->addMenuItem('logout','Logout');
		
		$admin_sub_menus_popover=$this->add('View_Popover');

		$admin_sub_menus = $admin_sub_menus_popover->add('Menu_Vertical');
		$admin_sub_menus->addMenuItem('staffs','Staff');
		$admin_sub_menus->addMenuItem('schemes',array('Schemes','swatch'=>'red','icon'=>'home'));
		$admin_sub_menus->addMenuItem('branches','Branches');
		$admin->js('click',$admin_sub_menus_popover->showJS());
		
	}
}