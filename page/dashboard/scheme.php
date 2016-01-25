<?php

class page_dashboard_scheme extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Scheme ','icon'=>'flag'));

		
		$tab=$this->add('Tabs');
		
		$cc=$tab->addTab('CC');
			$grid=$cc->add('Grid_AccountsBase');
			$scheme_cc_model =$cc->add('Model_Scheme_CC');
			$grid->setModel($scheme_cc_model,array('name','ActiveStatus','Interest','balance_sheet_id','balance_sheet','ProcessingFees','ProcessingFeesinPercent','SchemePoints','SchemeGroup','MinLimit','MaxLimit','total_accounts','total_active_accounts'));

		
		$dds=$tab->addTab('DDS');
			$scheme_dds_model =$dds->add('Model_Scheme_DDS');
			$grid=$dds->add('Grid_AccountsBase');
			$grid->setModel($scheme_dds_model,array('name','Interest','ActiveStatus','balance_sheet_id','MaturityPeriod','SchemeGroup','MinLimit','MaxLimit','CRPB','AccountOpenningCommission','CollectorCommissionRate'));
		
		$default=$tab->addTab('Default');
			$grid=$default->add('Grid_AccountsBase');
			$scheme_default_model =$default->add('Model_Scheme_Default');
			$grid->setModel($scheme_default_model,array('name','MinLimit','MaxLimit','ReducingOrFlatRate','ActiveStatus','balance_sheet_id','balance_sheet','ProcessingFees','SchemePoints','SchemeGroup','isDepriciable','DepriciationPercentBeforeSep','DepriciationPercentAfterSep','total_accounts','total_active_accounts'));
		
		$fixed=$tab->addTab('FixedAndMis');
			$grid=$fixed->add('Grid_AccountsBase');
			$scheme_FixedAndMis_model =$fixed->add('Model_Scheme_FixedAndMis');
			$scheme_FixedAndMis_model->setOrder('id','desc');
			$grid->setModel($scheme_FixedAndMis_model,array('type','name','Interest','InterestToAnotherAccount','AccountOpenningCommission','CRPB','ReducingOrFlatRate','ActiveStatus','balance_sheet_id','balance_sheet','MinLimit','MaxLimit','MaturityPeriod','ProcessingFeesinPercent','ProcessingFees','SchemeGroup','total_accounts','total_active_accounts'));
		
		$loan=$tab->addTab('Loan');
			$grid=$loan->add('Grid_AccountsBase');
			$scheme_Loan_model =$loan->add('Model_Scheme_Loan');
			$scheme_Loan_model->setOrder('id','desc');
			$grid->setModel($scheme_Loan_model,array('type','name','Interest','ReducingOrFlatRate','PremiumMode','NumberOfPremiums','ActiveStatus','balance_sheet','balance_sheet_id','ProcessingFees','ProcessingFeesinPercent','SchemeGroup','MinLimit','MaxLimit','total_accounts','total_active_accounts'));
		
		$rec=$tab->addTab('Recurring');
			$grid=$rec->add('Grid_AccountsBase');
			$scheme_Recurring_model =$rec->add('Model_Scheme_Recurring');
			$grid->setModel($scheme_Recurring_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','AccountOpenningCommission','CollectorCommissionRate','ActiveStatus','balance_sheet','balance_sheet_id','SchemeGroup','total_accounts','total_active_accounts'));
		
		$saving=$tab->addTab('SavingAndCurrent');
			$grid=$saving->add('Grid_AccountsBase');
			$scheme_SavingAndCurrent_model =$saving->add('Model_Scheme_SavingAndCurrent');
			$grid->setModel($scheme_SavingAndCurrent_model,array('type','name','MinLimit','MaxLimit','Interest','ActiveStatus','balance_sheet_id','SchemePoints','SchemeGroup','isDepriciable','DepriciationPercentBeforeSep','DepriciationPercentAfterSep','total_accounts','total_active_accounts'));

		$scheme_model=$this->add('Model_Scheme',array('table_alias'=>'xa'));

		$grid->setModel($scheme_model);
		$grid->addSno();
		$grid->addFormatter('name','wrap');

	}
}