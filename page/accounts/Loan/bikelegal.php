<?php

/**
* 
*/

class Model_Loan_BikeLegal extends Model_Account_Loan {}

class page_accounts_Loan_bikelegal extends Page{
	
	function init(){
		parent::init();

		$this->add('Controller_Acl');
		$model= $this->add('Model_Loan_BikeLegal');
		// $model->getElement('AccountNumber')->readOnly(true);

		$model->addHook('beforeSave',function($m){
			$error ='Please either fill both or none';

			$check_date = [
				'bike_surrendered'=>'bike_surrendered_on',
				'is_given_for_legal_process'=>'legal_process_given_date',
				'is_in_legal' =>'legal_filing_date',
				'is_godowncharge_debited' => 'godowncharge_debited_on',
				'is_legal_notice_sent_for_bike_auction'=>'legal_notice_sent_for_bike_auction_on',
				'is_bike_auctioned'=>'bike_auctioned_on',
				'is_final_recovery_notice_sent'=>'final_recovery_notice_sent_on',
				'is_cheque_presented_in_bank'=>'cheque_presented_in_bank_on',
				'is_cheque_returned'=>'cheque_returned_on',
				'is_notice_sent_after_cheque_returned'=>'notice_sent_after_cheque_returned_on',
				'is_legal_case_finalised'=>'legal_case_finalised_on',
				'is_bike_returned'=>'bike_returned_on',
				'is_society_notice_sent'=>'society_notice_sent_on',
				'is_visit_done'=>'visit_done_on',
				'is_noc_handling_charge_received'=>'noc_handling_charge_received_on',
			];

			foreach ($check_date as $key => $value) {
				if(($m[$key] && !$m[$value]) OR (!$m[$key] && $m[$value]))
					throw $m->exception($error,'ValidityCheck')->setField($key);
			}

		});

		$crud = $this->add('CRUD',['allow_add'=>false, 'allow_del'=>false]);
		$form_fields = ['bike_surrendered',
						'bike_surrendered_on',
						'bike_surrendered_by',
						'is_given_for_legal_process',
						'legal_process_given_date',
						'is_in_legal',
						'legal_filing_date',
						'is_godowncharge_debited',
						'godowncharge_debited_on',
						'is_legal_notice_sent_for_bike_auction',
						'legal_notice_sent_for_bike_auction_on',
						'is_bike_auctioned',
						'bike_auctioned_on',
						'is_final_recovery_notice_sent',
						'final_recovery_notice_sent_on',
						'is_cheque_presented_in_bank',
						'cheque_presented_in_bank_on',
						'is_cheque_returned',
						'cheque_returned_on',
						'is_notice_sent_after_cheque_returned',
						'notice_sent_after_cheque_returned_on',
						'is_legal_case_finalised',
						'legal_case_finalised_on',
						'is_bike_returned',
						'bike_returned_on',
						'bike_not_sold_reason',
						'legal_case_not_submitted_reason',
						'is_in_arbitration',
						'arbitration_on',
						'is_society_notice_sent',
						'society_notice_sent_on',
						'is_legal_notice_sent',
						'legal_notice_sent_on',
						'is_visit_done',
						'visit_done_on',
						'is_noc_handling_charge_received',
						'noc_handling_charge_received_on',
						];
						
		$grid_fields=array_merge(['AccountNumber'],$form_fields);;
		$crud->setModel($model,$form_fields,$grid_fields);
		$crud->add('Controller_Acl');

		$crud->grid->addPaginator(50);
		$crud->grid->addQuickSearch(['AccountNumber']);
	}
}