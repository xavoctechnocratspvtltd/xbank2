<?php

class page_schemes_DDS2 extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Scheme'));
		$scheme_dds2_model =$this->add('Model_Scheme_DDS2');
		$scheme_dds2_model->addCondition('type','DDS2');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$dds2_scheme_model = $crud->add('Model_Scheme_DDS2');
			try {
				$form->api->db->beginTransaction();
			    	$dds2_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_DDS, ACCOUNT_TYPE_DDS, $type="DDS2", $other_values=$form->getAllFields(),$form,$form->api->now);
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
			$scheme_dds2_model->hook('editing');
		}

		$crud->setModel($scheme_dds2_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','AccountOpenningCommission','CollectorCommissionRate','ActiveStatus','balance_sheet','balance_sheet_id','SchemeGroup','total_accounts','total_active_accounts','percent_loan_on_deposit','no_loan_on_deposit_till','pre_mature_interests','mature_interests_for_uncomplete_product','valid_till'));

		
		if($crud->grid){
			$crud->grid->addPaginator(50);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}