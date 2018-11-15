<?php

class page_agent_schemes extends Page {
	function init(){
		parent::init();

		$heading = $this->add('H2')->set(array('Scheme ','icon'=>'flag'));

		
		$tab=$this->add('Tabs');
		

		
		
		$rec=$tab->addTab('Recurring');
			$grid=$rec->add('Grid_Scheme');
			$scheme_Recurring_model =$rec->add('Model_Scheme_Recurring')->addCondition('ActiveStatus',true)->addCondition('valid_till','>=',$this->app->today);
			$grid->setModel($scheme_Recurring_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','percent_loan_on_deposit'));
		
		$fixed=$tab->addTab('FixedAndMis');
			$grid=$fixed->add('Grid_Scheme');
			$scheme_FixedAndMis_model =$fixed->add('Model_Scheme_FixedAndMis')->addCondition('ActiveStatus',true)->addCondition('valid_till','>=',$this->app->today);
			$scheme_FixedAndMis_model->setOrder('id','desc');
			$grid->setModel($scheme_FixedAndMis_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','percent_loan_on_deposit'));
		
		$dds=$tab->addTab('DDS');
			$scheme_dds_model =$dds->add('Model_Scheme_DDS')->addCondition('ActiveStatus',true)->addCondition('valid_till','>=',$this->app->today);
			$grid=$dds->add('Grid_Scheme');
			$grid->setModel($scheme_dds_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','percent_loan_on_deposit'));
		
		$saving=$tab->addTab('SavingAndCurrent');
			$grid=$saving->add('Grid_Scheme');
			$scheme_SavingAndCurrent_model =$saving->add('Model_Scheme_SavingAndCurrent')->addCondition('ActiveStatus',true)->addCondition('valid_till','>=',$this->app->today);
			$grid->setModel($scheme_SavingAndCurrent_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','percent_loan_on_deposit'));

		// $scheme_model=$this->add('Model_Scheme',array('table_alias'=>'xa'));

		// $grid->setModel($scheme_model);
		// $grid->addSno();
		// $grid->addFormatter('name','wrap');

	}
}