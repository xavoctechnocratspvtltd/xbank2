<?php

class Menu_Base extends Menu {
	function init(){
		parent::init();

		$this->addMenuItem('index','Dashboard');
		$admin = $this->addMenuItem('index','Super Admin');


		$this->addMenuItem('members','Members');
		$account = $this->addMenuItem('accounts','Accounts');
		
		$transaction = 	$this->addMenuItem('transactions','Transactions');
		$books = 	$this->addMenuItem('books','Books');
		$deposit_reports = 	$this->addMenuItem('reports_deposit','Deposit Reports');
		$utilities = 	$this->addMenuItem('utilities','Utilities');

		$this->addMenuItem('logout','Logout');

		// Popovers
		$admin_sub_menus_popover=$this->add('View_Popover');
		$account_sub_menu_popover = $this->add('View_Popover');
		$books_sub_menus_popover = $this->add('View_Popover');
		$transactions_sub_menus_popover = $this->add('View_Popover');
		$deposit_reports_sub_menus_popover = $this->add('View_Popover');
		$utilities_sub_menus_popover = $this->add('View_Popover');
		
		// Sub Menus

		$admin_sub_menus = $admin_sub_menus_popover->add('Menu_Vertical');
		$admin_sub_menus->addMenuItem('staffs','Staff');
		$admin_sub_menus->addMenuItem('schemes',array('Schemes','swatch'=>'red','icon'=>'home'));
		$admin_sub_menus->addMenuItem('branches','Branches');
		$admin->js('click',$admin_sub_menus_popover->showJS());

		$account_sub_menu = $account_sub_menu_popover->add('Menu_Vertical');
		$account_sub_menu->addMenuItem('accounts','Accounts Management');
		$account_sub_menu->addMenuItem('accounts_statement','Accounts Statement');
		$account->js('click',$account_sub_menu_popover->showJS());

		$transactions_sub_menus = $transactions_sub_menus_popover->add('Menu_Vertical');
		$transactions_sub_menus->addMenuItem('transactions_deposit','Deposit');
		$transactions_sub_menus->addMenuItem('transactions_withdrawl','WithDrawl');
		$transactions_sub_menus->addMenuItem('transactions_jv','Journal');
		$transactions_sub_menus->addMenuItem('transactions_forclose','ForClose');
		$transactions_sub_menus->addMenuItem('transactions_contra','Contra');
		$transaction->js('click',$transactions_sub_menus_popover->showJS());
		
		$books_sub_menus = $books_sub_menus_popover->add('Menu_Vertical');
		$books_sub_menus->addMenuItem('reports_daybook','Day Book');
		$books_sub_menus->addMenuItem('reports_cashbook','Cash Book');
		$books_sub_menus->addMenuItem('reports_balancesheet','Balance Sheet');
		$books_sub_menus->addMenuItem('reports_pandl','P & L');
		$books->js('click',$books_sub_menus_popover->showJS());

		$deposit_reports_sub_menus = $deposit_reports_sub_menus_popover->add('Menu_Vertical');
		$deposit_reports_sub_menus->addMenuItem('reports_deposit_rdCommissionAndTds','RD Comm & TDS');
		$deposit_reports->js('click',$deposit_reports_sub_menus_popover->showJS());

		$utilities_sub_menus = $utilities_sub_menus_popover->add('Menu_Vertical');
		$utilities_sub_menus->addMenuItem('documents','Documents Management');
		$utilities->js('click',$utilities_sub_menus_popover->showJS());

	}
}