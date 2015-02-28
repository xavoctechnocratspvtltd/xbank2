<?php

class Menu_Base extends Menu {
	function init(){
		parent::init();

		$this->addMenuItem('utility_setdate',array('('.$this->api->current_branch['Code']. ') ' .date('d M Y',strtotime($this->api->today)),'swatch'=>(strtotime($this->api->today) != strtotime(date('Y-m-d')) ? 'red':null )));

		$this->addMenuItem('index','Dashboard');
		$admin = $this->addMenuItem('index','Super Admin');


		$mad = $this->addMenuItem('#','M.A.D.');
		$stock = 	$this->addMenuItem('stock_main','Stock');
		$stock = 	$this->addMenuItem('staff_main','Staff Management');
		$account = $this->addMenuItem('accounts','Accounts');
		$transaction = 	$this->addMenuItem('transactions','Transactions');
		$reports = 	$this->addMenuItem('reports','Reports');
		// $books = 	$this->addMenuItem('books','Books');
		// $loan_reports = 	$this->addMenuItem('reports_loan','Loan Reports');
		// $agent_reports = 	$this->addMenuItem('reports_agent','Agent Reports');
		// $general = 	$this->addMenuItem('reports_general','General');
		// $member_reports = 	$this->addMenuItem('reports_member','Member Reports');
		// $deposit_reports = 	$this->addMenuItem('reports_deposit','Deposit Reports');
		$operations = 	$this->addMenuItem('operations','Operations');
		$utilities = 	$this->addMenuItem('utilities','Utilities');

		$this->addMenuItem('logout','Logout');

		// Popovers
		$admin_sub_menus_popover=$this->add('View_Popover');
		$mad_sub_menus_popover = $this->add('View_Popover');
		$account_sub_menu_popover = $this->add('View_Popover');

		$reports_sub_menu_popover = $this->add('View_Popover');
		
		// $books_sub_menus_popover = $this->add('View_Popover');
		$transactions_sub_menus_popover = $this->add('View_Popover');
		$loan_reports_sub_menus_popover = $this->add('View_Popover');
		$deposit_reports_sub_menus_popover = $this->add('View_Popover');
		$agent_reports_sub_menus_popover = $this->add('View_Popover');
		$operations_sub_menus_popover = $this->add('View_Popover');
		$utilities_sub_menus_popover = $this->add('View_Popover');
		$member_sub_menus_popover = $this->add('View_Popover');
		$general_sub_menus_popover = $this->add('View_Popover');
		
		// Sub Menus
		// 
		// 
		$reports_sub_menu = $reports_sub_menu_popover->add('Menu_Vertical');
		$reports_sub_menu->addMenuItem('reports_member','Member Reports');
		$reports_sub_menu->addMenuItem('reports_agent','Agent Reports');
		$reports_sub_menu->addMenuItem('reports_deleardsa','Dealer/DSA Reports');
		$reports_sub_menu->addMenuItem('reports_deposit','Deposit Reporst');
		$reports_sub_menu->addMenuItem('reports_loan','Loan & Recovery Reports');
		$reports_sub_menu->addMenuItem('reports_genral','General Reports');
		$reports_sub_menu->addMenuItem('reports_books','Books');
		$reports_sub_menu->addMenuItem('reports_search','Search Accounts');
		$reports->js('click',$reports_sub_menu_popover->showJS());
		

		$admin_sub_menus = $admin_sub_menus_popover->add('Menu_Vertical');
		$admin_sub_menus->addMenuItem('staffs','Staff');
		$admin_sub_menus->addMenuItem('schemes',array('Schemes','swatch'=>'red','icon'=>'home'));
		$admin_sub_menus->addMenuItem('branches','Branches');
		$admin_sub_menus->addMenuItem('mos','Mos');
		$admin_sub_menus->addMenuItem('team','Teams');
		$admin_sub_menus->addMenuItem('balancesheet','Top Heads');
		$admin_sub_menus->addMenuItem('accounts_locking','Lock & Unlock Accounts');
		$admin->js('click',$admin_sub_menus_popover->showJS());

		$mad_sub_menus = $mad_sub_menus_popover->add('Menu_Vertical');
		$mad_sub_menus->addMenuItem('members','Members');
		$mad_sub_menus->addMenuItem('agents','Agents');
		$mad_sub_menus->addMenuItem('dealers','Dealers');
		$mad_sub_menus->addMenuItem('dsa','DSA');
		$mad->js('click',$mad_sub_menus_popover->showJS());
		
		// $general_sub_menus = $general_sub_menus_popover->add('Menu_Vertical');
		// $general_sub_menus->addMenuItem('reports_general_document','Document Report');
		// $general_sub_menus->addMenuItem('reports_general_periodical','Periodical Report');
		// $general->js('click',$general_sub_menus_popover->showJS());

		$account_sub_menu = $account_sub_menu_popover->add('Menu_Vertical');
		$account_sub_menu->addMenuItem('accounts','Accounts Management');
		$account_sub_menu->addMenuItem('accounts_statement','Accounts Statement');
		$account->js('click',$account_sub_menu_popover->showJS());

		$transactions_sub_menus = $transactions_sub_menus_popover->add('Menu_Vertical');
		$transactions_sub_menus->addMenuItem('transactions_deposit','Deposit');
		$transactions_sub_menus->addMenuItem('transactions_withdrawl','WithDrawl');
		$transactions_sub_menus->addMenuItem('transactions_jv','Journal');
		$transactions_sub_menus->addMenuItem('transactions_forclose','ForClose');
		$transactions_sub_menus->addMenuItem('transactions_conveyance','Conveyance');
		$transactions_sub_menus->addMenuItem('transactions_fuel','Fuel');
		$transactions_sub_menus->addMenuItem('transactions_legalchargepaid','Legal Charge Paid');
		$transactions_sub_menus->addMenuItem('transactions_legalchargereceived','Legal Charge Received');
		$transactions_sub_menus->addMenuItem('transactions_visitcharge','Visit Charge');
		$transactions_sub_menus->addMenuItem('transactions_bankdeposit','Cash Bank Deposit');
		$transactions_sub_menus->addMenuItem('transactions_bankwithdrawl','Cash Bank Withdrawl');
		$transaction->js('click',$transactions_sub_menus_popover->showJS());
		
		// $books_sub_menus = $books_sub_menus_popover->add('Menu_Vertical');
		// $books_sub_menus->addMenuItem('reports_daybook','Day Book');
		// $books_sub_menus->addMenuItem('reports_cashbook','Cash Book');
		// $books_sub_menus->addMenuItem('reports_BSAndPANL','Balance Sheet');
		// $books_sub_menus->addMenuItem('reports_pandl','P & L');
		// $books->js('click',$books_sub_menus_popover->showJS());

		// $loan_reports_sub_menus = $loan_reports_sub_menus_popover->add('Menu_Vertical');
		// $loan_reports_sub_menus->addMenuItem('reports_loan_emiduelist','EMI Due List');
		// $loan_reports_sub_menus->addMenuItem('reports_loan_emireceivedlist','EMI Received List');
		// $loan_reports_sub_menus->addMenuItem('reports_loan_insuranceduelist','Insurance Due List');
		// $loan_reports_sub_menus->addMenuItem('reports_loan_dispatch','Loan Dispatch');
		// $loan_reports_sub_menus->addMenuItem('reports_loan_accountdetailed','Accont Detailed');
		// $loan_reports->js('click',$loan_reports_sub_menus_popover->showJS());

		// $agent_reports_sub_menus = $agent_reports_sub_menus_popover->add('Menu_Vertical');
		// $agent_reports_sub_menus->addMenuItem('reports_agent_tds','TDS Report');
		// $agent_reports_sub_menus->addMenuItem('reports_agent_status','Active/Inactive Report');
		// $agent_reports_sub_menus->addMenuItem('reports_agent_detail','Agent Detail');
		// $agent_reports->js('click',$agent_reports_sub_menus_popover->showJS());

		// $deposit_reports_sub_menus = $deposit_reports_sub_menus_popover->add('Menu_Vertical');
		// $deposit_reports->js('click',$deposit_reports_sub_menus_popover->showJS());



		$operations_sub_menus = $operations_sub_menus_popover->add('Menu_Vertical');
		$operations_sub_menus->addMenuItem('operations_edit','Edit Accounts');
		$operations->js('click',$operations_sub_menus_popover->showJS());

		$utilities_sub_menus = $utilities_sub_menus_popover->add('Menu_Vertical');
		$utilities_sub_menus->addMenuItem('documents','Documents Management');
		$utilities_sub_menus->addMenuItem('utility_setdate','Change Date');
		$utilities->js('click',$utilities_sub_menus_popover->showJS());

		// $member_sub_menus = $member_sub_menus_popover->add('Menu_Vertical');
		// $member_reports->js('click',$member_sub_menus_popover->showJS());
	}
}