<?php

class page_schemes_Recurring extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Scheme'));
		$scheme_Recurring_model =$this->add('Model_Scheme_Recurring');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$Recurring_scheme_model = $crud->add('Model_Scheme_Recurring');
			try {
				$form->api->db->beginTransaction();
			    	$Recurring_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_RECURRING, ACCOUNT_TYPE_RECURRING, $is_RecurringType=ACCOUNT_TYPE_RECURRING, $other_values=$form->getAllFields(),$form,$form->api->now);
			    $form->api->db->commit();
			} catch (Exception $e) {
			   	$form->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('edit')){
			$scheme_Recurring_model->hook('editing');
		}

		$crud->setModel($scheme_Recurring_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','AccountOpenningCommission','CollectorCommissionRate','ActiveStatus','balance_sheet','balance_sheet_id','SchemeGroup','total_accounts','total_active_accounts','percent_loan_on_deposit','no_loan_on_deposit_till','pre_mature_interests','mature_interests_for_uncomplete_product','valid_till'));

		
		if($crud->grid){
			$crud->grid->addPaginator(50);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}