<?php

class page_schemes_DDS extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Scheme'));
		$scheme_dds_model =$this->add('Model_Scheme_DDS');
		$scheme_dds_model->addCondition('type','DDS');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$dds_scheme_model = $crud->add('Model_Scheme_DDS');
			try {
				$form->api->db->beginTransaction();
			    	$dds_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_DDS, ACCOUNT_TYPE_DDS, $type=ACCOUNT_TYPE_DDS, $other_values=$form->getAllFields(),$form,$form->api->now);
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
			$scheme_dds_model->hook('editing');
		}

		$crud->setModel($scheme_dds_model,array('name','Interest','ActiveStatus','balance_sheet_id','MaturityPeriod','SchemeGroup','MinLimit','MaxLimit','CRPB','AccountOpenningCommission','CollectorCommissionRate','percent_loan_on_deposit','no_loan_on_deposit_till','pre_mature_interests','mature_interests_for_uncomplete_product','valid_till'));

		
		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addFormatter('CRPB','grid/inline');
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}