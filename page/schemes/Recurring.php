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
				$this->api->db->beginTransaction();
			    $Recurring_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_CC, ACCOUNT_TYPE_CC, $is_RecurringType=true, $other_values=$form->getAllFields(),$form,$form->api->now);
			    $this->api->db->commit();
			} catch (Exception $e) {
			   	$this->api->db->rollBack();
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

		$crud->setModel($scheme_Recurring_model,array('name','Interest','PremiumMode','NumberOfPremiums','MaturityPeriod','MinLimit','MaxLimit','CRPB','AccountOpenningCommission','CollectorCommissionRate','ActiveStatus','balance_sheet','balance_sheet_id','SchemeGroup','total_accounts','total_active_accounts'));

		
		if($crud->grid){
			$crud->grid->addPaginator(50);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}