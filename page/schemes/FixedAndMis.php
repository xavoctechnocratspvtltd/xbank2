<?php

class page_schemes_FixedAndMis extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		$scheme_FixedAndMis_model =$this->add('Model_Scheme_FixedAndMis');
		$scheme_FixedAndMis_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
			$FixedAndMis_scheme_model = $crud->add('Model_Scheme_FixedAndMis');
			try {
				$this->api->db->beginTransaction();
			    $FixedAndMis_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_FIXED, ACCOUNT_TYPE_FIXED, $is_loanType=true, $other_values=$form->getAllFields(),$form,$form->api->now);
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
			$scheme_FixedAndMis_model->hook('editing');
		}

		$crud->setModel($scheme_FixedAndMis_model,array('type','name','MinLimit','MaxLimit','Interest','AccountOpenningCommission','ReducingOrFlatRate','ActiveStatus','balance_sheet_id','InterestToAnotherAccount','MaturityPeriod','ProcessingFeesinPercent','ProcessingFees','SchemePoints','SchemeGroup'));

		
		if($crud->grid){
			$crud->grid->addPaginator(10);
		}

		if($crud->isEditing('add')){
			$o->now();
		}

		
	}
}