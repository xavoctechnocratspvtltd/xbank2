<?php

class page_dashboard_opened extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Account Open Today List','icon'=>'flag'));


		// SM Accounts
		$this->add('H3')->set('SM Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('AccountNumber','like','SM%');


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Cr'] - $bal['Dr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();


		// Saving And Current Accounts
		$this->add('H3')->set('Saving And Current Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType',ACCOUNT_TYPE_SAVING);


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Cr'] - $bal['Dr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// DDS Accounts
		$this->add('H3')->set('DDS Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType',ACCOUNT_TYPE_DDS);


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Cr'] - $bal['Dr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// RD Accounts
		$this->add('H3')->set('RD Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType','Recurring');


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Cr'] - $bal['Dr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// FD and MIS Accounts
		$this->add('H3')->set('Fixed And Mis Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType',ACCOUNT_TYPE_FIXED);


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Cr'] - $bal['Dr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// CC Accounts
		$this->add('H3')->set('CC Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType',ACCOUNT_TYPE_CC);


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Cr'] - $bal['Dr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// Loan Accounts
			// Loan PL
		$this->add('H3')->set('PL Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType','Loan');
		$accounts->addCondition('account_type',array('Personal Loan'));


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Dr'] - $bal['Cr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// SL loans

		$this->add('H3')->set('SL Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType','Loan');
		$accounts->addCondition('account_type',array('Loan Against Deposit'));


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Dr'] - $bal['Cr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

			// Loan VL Two Wheelers

		$this->add('H3')->set('VL Two Wheelers Loan Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType','Loan');
		$accounts->addCondition('account_type',array('Two Wheeler Loan'));


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Dr'] - $bal['Cr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// 'Auto Loan'

		$this->add('H3')->set('VL Auto (Four Wheelers) Loan Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType','Loan');
		$accounts->addCondition('account_type',array('Auto Loan'));


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Dr'] - $bal['Cr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// Other loans

		$this->add('H3')->set('Other Loan Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType','Loan');
		$accounts->addCondition('account_type','<>',array('Auto Loan','Two Wheeler Loan','Loan Against Deposit','Personal Loan'));


		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Dr'] - $bal['Cr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

		// Other Accounts Default

		$this->add('H3')->set('All Other Accounts');
		$grid=$this->add('Grid_AccountsBase');
		$accounts=$this->add('Model_Active_Account');
		$accounts->addCondition('created_at','>=',$this->api->today);
		$accounts->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		
		$accounts->addCondition('SchemeType','Default');
		$accounts->addCondition('AccountNumber','not like','SM%');

		$member_j = $accounts->join('members','member_id');

		$member_j->addField('member_name','name');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');

		$accounts->add('Controller_Acl');
		$accounts->setOrder('AccountNumber');

		$grid->setModel($accounts,array('AccountNumber','member_name','FatherName','PermanentAddress','PhoneNos','scheme', 'Amount','DueDate','agent','dealer'));

		$grid->addMethod('format_crbal',function($grid,$field){
			
			try{
				$bal = $grid->model->getOpeningBalance($grid->api->nextDate($grid->api->today));
			}catch(Exception $e){
				$grid->current_row[$field]=0;
				return;
			}

			$bal_fig = $bal['Dr'] - $bal['Cr'];
			$grid->current_row[$field] = $bal_fig;
		});

		$grid->addColumn('crbal','balance_on_date');
		$grid->addTotals(array('balance_on_date','Amount'));
		$grid->addSno();

	}
}