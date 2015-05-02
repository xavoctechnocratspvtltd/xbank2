<?php

class page_t extends Page{
	function init(){
		parent::init();
		$loan_scheme = $this->add('Model_Scheme_Loan');
		$syra = $this->add('Model_Branch')->load(9);
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-01');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-02');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-03');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-04');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-05');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-06');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-07');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-08');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-09');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-10');
		$loan_scheme->putPaneltiesOnAllUnpaidLoanPremiums($syra,'2015-04-11');
	}
}