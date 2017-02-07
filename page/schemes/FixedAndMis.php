<?php

class page_schemes_FixedAndMis extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Scheme'));
		$scheme_FixedAndMis_model =$this->add('Model_Scheme_FixedAndMis');
		$scheme_FixedAndMis_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			$FixedAndMis_scheme_model = $crud->add('Model_Scheme_FixedAndMis');
			try {
				$crud->api->db->beginTransaction();
			    $FixedAndMis_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_FIXED, ACCOUNT_TYPE_FIXED, $loanType_if_loan=$form['type'], $other_values=$form->getAllFields(),$form,$form->api->now);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('edit')){
			$scheme_FixedAndMis_model->hook('editing');
		}

		$crud->setModel($scheme_FixedAndMis_model,array('type','name','Interest','InterestToAnotherAccount','AccountOpenningCommission','CRPB','ReducingOrFlatRate','ActiveStatus','balance_sheet_id','balance_sheet','MinLimit','MaxLimit','MaturityPeriod','ProcessingFeesinPercent','ProcessingFees','SchemeGroup','total_accounts','total_active_accounts','percent_loan_on_deposit','no_loan_on_deposit_till','pre_mature_interests','valid_till'));

		
		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('name'));
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}