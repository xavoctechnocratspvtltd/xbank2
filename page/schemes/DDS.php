<?php

class page_schemes_DDS extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$scheme_dds_model =$this->add('Model_Scheme_DDS');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$dds_scheme_model = $crud->add('Model_Scheme_DDS');
			try {
				$this->api->db->beginTransaction();
			    $dds_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_DDS, ACCOUNT_TYPE_DDS, $is_loanType=true, $other_values=$form->getAllFields(),$form,$form->api->now);
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
			$scheme_dds_model->hook('editing');
		}

		$crud->setModel($scheme_dds_model,array('name','MinLimit','MaxLimit','Interest','AccountOpenningCommission','ActiveStatus','balance_sheet_id','MaturityPeriod','SchemePoints','SchemeGroup'));

		
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}