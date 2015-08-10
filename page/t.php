<?php

class page_t extends Page{
	function init(){
		parent::init();

		$sm_accounts = $this->add('Model_Account_SM');
		$sm_accounts->addExpression('should_be')->set(function($m,$q){
			return $q->expr("
				CAST(RIGHT(
						AccountNumber,
						LENGTH(AccountNumber) - 2
						) AS UNSIGNED
				)");
		});

		$sm_accounts->addCondition('should_be','>=',90000);
		$sm_accounts->setOrder('should_be');

		// foreach ($sm_accounts as $sm) {
		// 	$sm_accounts['AccountNumber'] = "SM". ( $sm_accounts['should_be'] - 78628 );
		// 	$sm_accounts->saveAndUnload();
		// }

		$g = $this->add('Grid');
		$g->setModel($sm_accounts,array("AccountNumber","should_be"));
		$g->addPaginator(200);
	}
}