<?php

class Menu_Base extends Menu {
	function init(){
		parent::init();
		$this->addClass('noneprintalbe');
		// $this->add('View')->setElement('img')->setAttr('src','templates/images/logo.jpg')->setAttr('width','30px');
		// if($this->api->auth->model['AccessLevel']>=80)
			$this->addMenuItem('utility_setdate',array('('. date('d M Y',strtotime($this->api->today)).') ','swatch'=>(strtotime($this->api->today) != strtotime(date('Y-m-d')) ? 'red':null )));
			// '('.$this->api->current_branch['Code']. ') ' .

		$branch_v = $this->api->add('View',null,'BankBanner');
		$branch_v->setHTML('Branch : '. $this->api->currentBranch['name'])->addStyle('margin-top:-5px;font-size:15px;');

		$this->addMenuItem('index','Dashboard');
		
		if($this->api->currentStaff->isSuper())
			$admin = $this->addMenuItem('index','Super Admin');


		$hod = $this->addMenuItem('#','HOD Authority');
		$purchase = $this->addMenuItem('#','GST');
		$mad = $this->addMenuItem('#','M.A.D.');
		$stock = 	$this->addMenuItem('stock_main','Stock');
		// $stock = 	$this->addMenuItem('staff_main','Staff Management');
		$account = $this->addMenuItem('accounts','Accounts');
		$transaction = 	$this->addMenuItem('transactions','Transactions');
		$reports = 	$this->addMenuItem('reports','Reports');
		// $books = 	$this->addMenuItem('books','Books');
		// $loan_reports = 	$this->addMenuItem('reports_loan','Loan Reports');
		// $agent_reports = 	$this->addMenuItem('reports_agent','Agent Reports');
		// $general = 	$this->addMenuItem('reports_general','General');
		// $member_reports = 	$this->addMenuItem('reports_member','Member Reports');
		// $deposit_reports = 	$this->addMenuItem('reports_deposit','Deposit Reports');
		$utilities = 	$this->addMenuItem('utilities','Pirinting');

		$this->addMenuItem('logout',$this->app->auth->model['username'].' | Logout')->addClass('atk-swatch-red');
		// $this->addMenuItem('index','Bhawani Credit Co.Operative Society')->setStyle('text-align','center');
		// Popovers
		if($this->api->currentStaff->isSuper())
			$admin_sub_menus_popover=$this->add('View_Popover');

		$hod_sub_menus_popover = $this->add('View_Popover');
		$mad_sub_menus_popover = $this->add('View_Popover');
		$stock_sub_menus_popover = $this->add('View_Popover');
		$account_sub_menu_popover = $this->add('View_Popover');
		$purchase_sub_menus_popover = $this->add('View_Popover');
		$reports_sub_menu_popover = $this->add('View_Popover');
		
		// $books_sub_menus_popover = $this->add('View_Popover');
		$transactions_sub_menus_popover = $this->add('View_Popover');
		$loan_reports_sub_menus_popover = $this->add('View_Popover');
		$deposit_reports_sub_menus_popover = $this->add('View_Popover');
		$agent_reports_sub_menus_popover = $this->add('View_Popover');
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
		$reports_sub_menu->addMenuItem('reports_deposit','Deposit Reports');
		$reports_sub_menu->addMenuItem('reports_loan','Loan Reports');
		$reports_sub_menu->addMenuItem('reports_recovery','Recovery Reports');
		$reports_sub_menu->addMenuItem('reports_genral','General Reports');
		$reports_sub_menu->addMenuItem('reports_books','Books');
		$reports_sub_menu->addMenuItem('reports_employee','Employee');
		$reports_sub_menu->addMenuItem('reports_loan_bikelegal','Bike & Legal Reports');
		$reports_sub_menu->addMenuItem('reports_share_sharereports','Share Reports');
		// $reports_sub_menu->addMenuItem('reports_search','Search Accounts');
		$reports->js('click',$reports_sub_menu_popover->showJS());
		
		if($this->api->currentStaff->isSuper()){
			$admin_sub_menus = $admin_sub_menus_popover->add('Menu_Vertical');
			$admin_sub_menus->addMenuItem('staff_main','Staff');
			$admin_sub_menus->addMenuItem('schemes',array('Schemes','swatch'=>'red','icon'=>'home'));
			$admin_sub_menus->addMenuItem('branches','Branches');
			$admin_sub_menus->addMenuItem('balancesheet','Top Heads');
			$admin_sub_menus->addMenuItem('agentscadre','Agents Cadres');
			// $admin_sub_menus->addMenuItem('operations_edit','Edit Accounts');
			// $admin_sub_menus->addMenuItem('utility_crpbedit','CRPB Edit');
			$admin_sub_menus->addMenuItem('closingnew','Closing');
			$admin_sub_menus->addMenuItem('contentmanagement','Content Management');
			$admin_sub_menus->addMenuItem('share_management','Share Management');
			$admin->js('click',$admin_sub_menus_popover->showJS());
		}

		$hod_auth_menu = $hod_sub_menus_popover->add('Menu_Vertical');
		$hod_auth_menu->addMenuItem('mos','Mos/Ros/TeleCallers');
		$hod_auth_menu->addMenuItem('team','Teams');
		$hod_auth_menu->addMenuItem('accounts_locking','Lock & Unlock Accounts');
		$hod_auth_menu->addMenuItem('transactions_remove','Edit/Delete Transaction');
		$hod_auth_menu->addMenuItem('documents','Documents Management');
		$hod_auth_menu->addMenuItem('utility_bankslist','Banks List');
		$hod_auth_menu->addMenuItem('utility_premimumtable','Premimum Table');
		$hod_auth_menu->addMenuItem('utility_odlimit','Over Draft Limit');
		$hod_auth_menu->addMenuItem('log','Log Check');
		$hod->js('click',$hod_sub_menus_popover->showJS());

		$purchase_auth_menu = $purchase_sub_menus_popover->add('Menu_Vertical');
		$purchase_auth_menu->addMenuItem('supplier','Supplier');
		$purchase_auth_menu->addMenuItem('transactions_purchase','Purchase');
		$purchase_auth_menu->addMenuItem('memorandum_charge','Memorandum Apply');
		$purchase_auth_menu->addMenuItem('memorandum_deposite','GST Deposite');
		$purchase_auth_menu->addMenuItem('memorandum_statement','Account Statement');
		$purchase_auth_menu->addMenuItem('memorandum_generalgst','General GST');
		$purchase_auth_menu->addMenuItem('memorandum_report','Report');
		$purchase->js('click',$purchase_sub_menus_popover->showJS());
		
		$mad_sub_menus = $mad_sub_menus_popover->add('Menu_Vertical');
		$mad_sub_menus->addMenuItem('members','Members');
		$mad_sub_menus->addMenuItem('agents','Agents');
		$mad_sub_menus->addMenuItem('dealers','Dealers');
		$mad_sub_menus->addMenuItem('dsa','DSA');
		
		if($this->api->auth->model['AccessLevel'] >= 80)
			$mad_sub_menus->addMenuItem('employee','Employees');
		
		$mad_sub_menus->addMenuItem('insurancemember','Member Insurance');

		$mad->js('click',$mad_sub_menus_popover->showJS());
		
		// $general_sub_menus = $general_sub_menus_popover->add('Menu_Vertical');
		// $general_sub_menus->addMenuItem('reports_general_document','Document Report');
		// $general_sub_menus->addMenuItem('reports_general_periodical','Periodical Report');
		// $general->js('click',$general_sub_menus_popover->showJS());

		$stock_sub_menus = $stock_sub_menus_popover->add('Menu_Vertical');
		$stock_sub_menus->addMenuItem('stock_main','Stock Old');
		$stock_sub_menus->addMenuItem('stocknew_main','Stock New');
		$stock->js('click',$stock_sub_menus_popover->showJS());


		$account_sub_menu = $account_sub_menu_popover->add('Menu_Vertical');
		$account_sub_menu->addMenuItem('accounts','Accounts Management');
		$account_sub_menu->addMenuItem('accounts_statement','Accounts Statement');
		// $account_sub_menu->addMenuItem('utility_accwithoutimage','Accounts Without Images');
		$account_sub_menu->addMenuItem('noclog','NOC Management');
		$account->js('click',$account_sub_menu_popover->showJS());

		$transactions_sub_menus = $transactions_sub_menus_popover->add('Menu_Vertical');
		$transactions_sub_menus->addMenuItem('transactions_deposit','Deposit');
		$transactions_sub_menus->addMenuItem('transactions_withdrawl','WithDrawl');
		$transactions_sub_menus->addMenuItem('transactions_jv','Journal (Transfer)');
		$transactions_sub_menus->addMenuItem('transactions_forclose','ForClose');
		$transactions_sub_menus->addMenuItem('transactions_conveyance','Conveyance');
		$transactions_sub_menus->addMenuItem('transactions_fuel','Fuel');
		$transactions_sub_menus->addMenuItem('transactions_penalty','Penalty');
		// $transactions_sub_menus->addMenuItem('transactions_legalchargepaid','Legal Charge Paid');
		// $transactions_sub_menus->addMenuItem('transactions_legalchargereceived','Legal Charge Received');
		// $transactions_sub_menus->addMenuItem('transactions_visitcharge','Visit Charge');
		// $transactions_sub_menus->addMenuItem('transactions_recoveryandlegaltransactions','Recovery and Legal Transaction');
		$transactions_sub_menus->addMenuItem('transactions_bankdeposit','Cash Bank Deposit');
		$transactions_sub_menus->addMenuItem('transactions_bankwithdrawl','Cash Bank Withdrawl');
		$transactions_sub_menus->addMenuItem('transactions_premature','Pre Mature Payments');
		$transactions_sub_menus->addMenuItem('transactions_salaryandallowances','Salary & Allowances');
		$transactions_sub_menus->addMenuItem('transactions_sharetransactions','Share Transactions');
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




		$utilities_sub_menus = $utilities_sub_menus_popover->add('Menu_Vertical');
		
		// $utilities_sub_menus->addMenuItem('utility_setdate','Change Date');
		
		$utilities_sub_menus->addMenuItem('utility_fdaccount','F.D. Bond Accounts');
		$utilities_sub_menus->addMenuItem('utility_sharecertificate','Share Certificate Print');
		$utilities_sub_menus->addMenuItem('utility_intrestcertificate','Intrest Certificate Print');
		$utilities_sub_menus->addMenuItem('utility_printaccountcontent','Print File Content');
		
		$utilities->js('click',$utilities_sub_menus_popover->showJS());

		// $member_sub_menus = $member_sub_menus_popover->add('Menu_Vertical');
		// $member_reports->js('click',$member_sub_menus_popover->showJS());
	}
}