<?php

class page_schemes_CC extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Scheme'));
		$scheme_cc_model =$this->add('Model_Scheme_CC');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$cc_scheme_model = $crud->add('Model_Scheme_CC');
			try {
				$this->api->db->beginTransaction();
			    $cc_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_CC, ACCOUNT_TYPE_CC, $is_loanType=ACCOUNT_TYPE_CC, $other_values=$form->getAllFields(),$form,$form->api->now);
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
			$scheme_cc_model->hook('editing');
		}

		$crud->setModel($scheme_cc_model,array('name','ActiveStatus','Interest','balance_sheet_id','balance_sheet','ProcessingFees','ProcessingFeesinPercent','SchemePoints','SchemeGroup','MinLimit','MaxLimit','total_accounts','total_active_accounts','valid_till'));

		
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}