<?php

class Menu_Base extends Menu {
	function init(){
		parent::init();

		$this->addMenuItem('index','Dashboard');
		$admin = $this->addMenuItem('index','Super Admin');


		$this->addMenuItem('members','Members');
		$this->addMenuItem('accounts','Accounts');
		
		$transaction = 	$this->addMenuItem('transactions','Transactions');
		$books = 	$this->addMenuItem('books','Books');

		$this->addMenuItem('logout','Logout');

		$admin_sub_menus_popover=$this->add('View_Popover');
		$transactions_sub_menus_popover = $this->add('View_Popover');
		$books_sub_menus_popover = $this->add('View_Popover');
		

		$admin_sub_menus = $admin_sub_menus_popover->add('Menu_Vertical');
		$admin_sub_menus->addMenuItem('staffs','Staff');
		$admin_sub_menus->addMenuItem('schemes',array('Schemes','swatch'=>'red','icon'=>'home'));
		$admin_sub_menus->addMenuItem('branches','Branches');
		$admin->js('click',$admin_sub_menus_popover->showJS());

		$transactions_sub_menus = $transactions_sub_menus_popover->add('Menu_Vertical');
		$transactions_sub_menus->addMenuItem('transactions_deposit','Deposit');
		$transactions_sub_menus->addMenuItem('transactions_withdrawl','WithDrawl');
		$transactions_sub_menus->addMenuItem('transactions_jv','Journal');
		$transactions_sub_menus->addMenuItem('transactions_forclose','ForClose');
		$transactions_sub_menus->addMenuItem('transactions_contra','Contra');
		$transaction->js('click',$transactions_sub_menus_popover->showJS());
		
		$books_sub_menus = $books_sub_menus_popover->add('Menu_Vertical');
		$books_sub_menus->addMenuItem('daybook','Day Book');
		$books_sub_menus->addMenuItem('cashbook','Cash Book');
		$books->js('click',$books_sub_menus_popover->showJS());
	}
}