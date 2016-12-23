<?php

class page_debug extends Page {
	function init(){
		parent::init();
		
		$this->add('H2')->set('Mismatched Transactions');

		$model = $this->add('Model_Transaction');
		$model->_dsql()->having('cr_sum','<>',$model->dsql()->expr('dr_sum'));

		$g = $this->add('Grid_AccountsBase');
		$g->addSno();
		$g->setModel($model,array('id','branch','created_at','voucher_no','cr_sum','dr_sum'));
		$g->addPaginator(100);
		$g->addTotals(array('dr_sum','cr_sum'));

		$g->addFormatter('voucher_no','voucherNo');

		$this->add('H2')->set('Accounts Not Haviong Cr/Dr in account table');

		$acc = $this->add('Model_Account');
		$acc->addExpression('calculated_cr_sum')->set($acc->refSQL('TransactionRow')->sum('amountCr'));
		$acc->addExpression('calculated_dr_sum')->set($acc->refSQL('TransactionRow')->sum('amountDr'));

		$acc->addCondition($acc->dsql()->expr("[0]<> ([1]-[2]) OR [3] <> ([4]-[5])",
					array(
						$acc->getElement('calculated_cr_sum'),
						$acc->getElement('CurrentBalanceCr'),
						$acc->getElement('OpeningBalanceCr'),
						$acc->getElement('calculated_dr_sum'),
						$acc->getElement('CurrentBalanceDr'),
						$acc->getElement('OpeningBalanceDr')
						)
					)
			);
		$g = $this->add('Grid');
		$g->setModel($acc,array('AccountNumber','CurrentBalanceCr','calculated_cr_sum','CurrentBalanceDr','calculated_dr_sum'));
		$g->addPaginator(50);
		$g->addQuickSearch(array('AccountNumber'));

		// $model = $this->add('Model_Transaction');
		// $model->addCondition('reference_id',null);
		// $model->addCondition('transaction_type','<>',TRA_JV_ENTRY);
		// $model->addCondition('transaction_type','<>','NewMemberRegistrationAmunt');
		// $model->addCondition('transaction_type','<>','NewMemberRegistrationAmount');
		// $model->addCondition('transaction_type','<>','DepriciationAmountCalculated');

		// $g = $this->add('Grid_AccountsBase');
		// $g->setModel($model,array('id','voucher_no','cr_sum','dr_sum'));
		// $g->addPaginator(100);
		// $g->addTotals(array('dr_sum','cr_sum'));

		// $g->addFormatter('voucher_no','voucherNo');

		// $this->add('H2')->set('Wrong currentBalances');

		// $model = $this->add('Model_Account');
		// $model->addExpression('cr_sum')->set($model->refSQL('TransactionRow')->sum('amountCr'));
		// $model->addExpression('dr_sum')->set($model->refSQL('TransactionRow')->sum('amountDr'));

		// if(strpos($_GET['submit'], 'inline') === false){
		// 	$model->_dsql()->having(array(
		// 			array($model->dsql()->expr('cr_sum+OpeningBalanceCr'),'<>',$model->getElement('CurrentBalanceCr')),
		// 			array($model->dsql()->expr('dr_sum+OpeningBalanceDr'),'<>',$model->getElement('CurrentBalanceDr'))
		// 				)
		// 			);

		// }

		// // $model->addCondition('dr_sum','<>',$model->getElement('CurrentBalanceDr'));

		// $g = $this->add('Grid_AccountsBase');
		// $g->setModel($model,array('AccountNumber','OpeningBalanceCr','OpeningBalanceDr','CurrentBalanceCr','cr_sum','CurrentBalanceDr','dr_sum'));
		// $g->addPaginator(100);

		// $g->addFormatter('CurrentBalanceCr','grid/inline');
		// $g->addFormatter('CurrentBalanceDr','grid/inline');



	}
}