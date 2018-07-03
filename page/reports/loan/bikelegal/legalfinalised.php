<?php

class page_reports_loan_bikelegal_legalfinalised extends Page {
	public $title="Legal Finalised Report";
	
	function init(){
		parent::init();

		$form= $this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addField('dropdown','legal_status')->setValueList(array('all'=>'All','is_in_legal'=>'Is In Legal','is_given_for_legal_process'=>'Is In Legal Process','is_in_arbitration'=>'Is In Arbitration'));

		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('All');
		$dealer_field->setModel('ActiveDealer');

		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}

		$form->addSubmit('Get List');

		$account_model = $this->add('Model_Account_Loan');
		$account_model->addCondition('DefaultAC',false);

		$member_j = $account_model->join('members','member_id');
		$member_j->addField('FatherName');
		$member_j->addField('PermanentAddress');
		$member_j->addField('PhoneNos');
		$member_j->addField('landmark');
		$member_j->addField('tehsil');
		$member_j->addField('district');

		$account_model->addExpression('member_sm_account')->set(function($m,$q){
			return  $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])->addCondition('member_id',$q->getField('member_id'))->setLimit(1)->fieldQuery('AccountNumber');
		});

		$account_model->addExpression('no_of_emi')->set(function($m,$q){
			return $m->refSQL('Premium')->count();
		});

		$account_model->addExpression('emi_amount')->set(function($m,$q){
			return $m->refSQL('Premium')->setLImit(1)->fieldQuery('Amount');
		});

		// $account_model->addExpression('due_premium_count')->set(function($m,$q){
		// 	$p_m = $m->refSQL('Premium')
		// 				->addCondition('PaidOn',null);
		// 	$p_m->addCondition('DueDate','<',$m->api->nextDate($this->app->now));
		// 	return $p_m->count();
		// });

		// $account_model->addExpression('paid_premium_count')->set(function($m,$q){
		// 	$p_m=$m->refSQL('Premium')
		// 				->addCondition('PaidOn','<>',null);
		// 	$p_m->addCondition('DueDate','<',$m->api->nextDate($this->app->today));
		// 	return $p_m->count();
		// })->sortable(true);

		// $account_model->addExpression('due_premium_amount')->set(function($m,$q){
		// 	return $q->expr('[0]*[1]',[$m->getElement('due_premium_count'),$m->getElement('emi_amount')]);
		// });

		// $account_model->addExpression('due_panelty')->set(function($m,$q){
		// 	$trans_type = $this->add('Model_TransactionType')->tryLoadBy('name',TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT);
			
		// 	$tr_m_due = $m->add('Model_TransactionRow',array('table_alias'=>'charged_panelty_tr'));
		// 	$tr_m_due->addCondition('transaction_type_id',$trans_type->id); 
		// 	$tr_m_due->addCondition('account_id',$q->getField('id'));
		// 	$tr_m_due->addCondition('created_at','<',$this->app->nextDate($this->app->today));

		// 	$trans_type = $this->add('Model_TransactionType')->tryLoadBy('name',TRA_PENALTY_AMOUNT_RECEIVED);
			
		// 	$tr_m_received = $m->add('Model_TransactionRow',array('table_alias'=>'received_panelty_tr'));
		// 	$tr_m_received->addCondition('transaction_type_id',$trans_type->id); 
		// 	$tr_m_received->addCondition('account_id',$q->getField('id'));
		// 	$tr_m_received->addCondition('created_at','<',$this->app->nextDate($this->app->today));

		// 	return $q->expr('(IFNULL([0],0)-IFNULL([1],0))',[$tr_m_due->sum('amountDr'),$tr_m_received->sum('amountCr')]);
		// });

		// $account_model->addExpression('total_cr')->set(function($m,$q){
		// 	$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
		// 	$tr_m->addCondition('account_id',$q->getField('id'));
		// 	return $received = $tr_m->sum('amountCr');
		// });

		// $account_model->addExpression('other_charges')->set(function($m,$q){
		// 	$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
		// 	$tr_m->addCondition('transaction_type_id',[13, 46, 39]); // JV, TRA_VISIT_CHARGE, LegalChargeReceived
		// 	$tr_m->addCondition('account_id',$q->getField('id'));
		// 	return $tr_m->sum('amountDr');
		// });

		// $account_model->addExpression('premium_amount_received')->set(function($m,$q){
		// 	return $premium_paid = $q->expr('([0]*[1])',[$m->getElement('paid_premium_count'),$m->getElement('emi_amount')]);
		// });

		// $account_model->addExpression('penalty_amount_received')->set(function($m,$q){
		// 	$trans_type = $this->add('Model_TransactionType')->tryLoadBy('name',TRA_PENALTY_AMOUNT_RECEIVED);
			
		// 	$tr_m_received = $m->add('Model_TransactionRow',array('table_alias'=>'other_received_panelty_tr'));
		// 	$tr_m_received->addCondition('transaction_type_id',$trans_type->id); 
		// 	$tr_m_received->addCondition('account_id',$q->getField('id'));
		// 	$tr_m_received->addCondition('created_at','<',$this->app->nextDate($this->app->today));
		// 	return $tr_m_received->sum('amountCr');
		// });

		// $account_model->addExpression('other_received')->set(function($m,$q){
		// 	return $q->expr('(IFNULL([0],0)-(IFNULL([1],0)+IFNULL([2],0)))',[$m->getElement('total_cr'),$m->getElement('premium_amount_received'),$m->getElement('penalty_amount_received')]);
		// });

		// $account_model->addExpression('other_charges_due')->set(function($m,$q){
		// 	return $q->expr('(IFNULL([0],0)-IFNULL([1],0))',[$m->getElement('other_charges'),$m->getElement('other_received')]);
		// });

		// $account_model->addExpression('total_due')->set(function($m,$q){
		// 	return $q->expr('(IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0))',[$m->getElement('due_premium_amount'),$m->getElement('due_panelty'),$m->getElement('other_charges_due')]);
		// });

		$grid_column_array = ['AccountNumber','member','FatherName','PermanentAddress','landmark','tehsil','district','PhoneNos','dealer','member_sm_account','bike_surrendered_on','Amount','no_of_emi','created_at','legal_process_given_date','legal_filing_date','legal_case_finalised_on','is_in_legal','is_given_for_legal_process'];

		if($this->api->stickyGET('filter')){
			if($this->api->stickyGET('dealer')){
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($this->api->stickyGET('doc_'.$document->id)){
					$this->api->stickyGET('doc_'.$document->id);
					$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}

			if($d=$this->app->stickyGET('from_date')){
				$account_model->addCondition('legal_case_finalised_on','>=',$d);
			}

			if($d=$this->app->stickyGET('to_date')){
				$account_model->addCondition('legal_case_finalised_on','<',$this->app->nextDate($d));
			}

			$this->api->stickyGET('legal_status');
			switch ($_GET['legal_status']) {
				case 'is_in_legal':
					$account_model->addCondition('is_in_legal',true);
					$account_model->addCondition('is_given_for_legal_process',true);
					$account_model->addCondition('is_legal_case_finalised',true);
					break;
				
				case 'is_given_for_legal_process':
					$account_model->addCondition('is_in_legal',false);
					$account_model->addCondition('is_in_arbitration',false);
					$account_model->addCondition('is_given_for_legal_process',true);
					$account_model->addCondition('is_legal_case_finalised',true);
					break;
				case 'is_in_arbitration':
					$account_model->addCondition('is_in_legal',false);
					$account_model->addCondition('is_in_arbitration',true);
					$account_model->addCondition('is_legal_case_finalised',true);
					$account_model->addCondition('is_given_for_legal_process',true);
				case 'all':
					$account_model->addCondition([['is_given_for_legal_process',true],['is_in_legal',true],['is_in_arbitration',true]]);
					$account_model->addCondition('is_legal_case_finalised',true);
				default:
					# code...
					break;
			}
		}else{
			$account_model->addCondition('id',-1);
		}

		// $account_model->addCondition([['cheque_returned_on','<>',""],['cheque_returned_on','<>',null]]);

		$grid = $this->add('Grid_AccountsBase')->addSno();

		$grid->setModel($account_model,$grid_column_array);
		$grid->addPaginator(100);

		$grid->removeColumn('is_in_legal');
		$grid->removeColumn('is_given_for_legal_process');


		if($form->isSubmitted()){
			$send = array('filter'=>1,'dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'legal_status'=>$form['legal_status']);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();
		}

	}
}