<?php

class page_debug extends Page {
	function init(){
		parent::init();
		
		$this->add('H2')->set('Mismatched Transactions');

		$model = $this->add('Model_Transaction');
		$model->_dsql()->having('cr_sum','<>',$model->dsql()->expr('dr_sum'));

		$g = $this->add('Grid_AccountsBase');
		$g->setModel($model,array('id','voucher_no','cr_sum','dr_sum'));
		$g->addPaginator(100);
		$g->addTotals(array('dr_sum','cr_sum'));

		$g->addFormatter('voucher_no','voucherNo');

		$model = $this->add('Model_Transaction');
		$model->addCondition('reference_id',null);
		$model->addCondition('transaction_type','<>',TRA_JV_ENTRY);
		$model->addCondition('transaction_type','<>','NewMemberRegistrationAmunt');
		$model->addCondition('transaction_type','<>','NewMemberRegistrationAmount');
		$model->addCondition('transaction_type','<>','DepriciationAmountCalculated');

		$g = $this->add('Grid_AccountsBase');
		$g->setModel($model,array('id','voucher_no','cr_sum','dr_sum'));
		$g->addPaginator(100);
		$g->addTotals(array('dr_sum','cr_sum'));

		$g->addFormatter('voucher_no','voucherNo');


	}
}