<?php

class page_schemes_Default extends Page{
	function init(){
		parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Scheme'));
		$scheme_default_model =$this->add('Model_Scheme_Default');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$default_scheme_model = $crud->add('Model_Scheme_Default');
			try {
				$crud->api->db->beginTransaction();
			    $default_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_DEFAULT, ACCOUNT_TYPE_DEFAULT, $is_loanType=true, $other_values=$form->getAllFields(),$form,$form->api->now);
			    $crud->api->db->commit();
			} catch (Exception $e) {
			   	$crud->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});

		if($crud->isEditing()){
		    $o=$crud->form->add('Order');
		}

		if($crud->isEditing('edit')){
			$scheme_default_model->hook('editing');
		}

		$crud->setModel($scheme_default_model,array('name','MinLimit','MaxLimit','ReducingOrFlatRate','ActiveStatus','balance_sheet_id','balance_sheet','ProcessingFees','SchemePoints','SchemeGroup','isDepriciable','DepriciationPercentBeforeSep','DepriciationPercentAfterSep','total_accounts','total_active_accounts','valid_till'));

		
		if($crud->grid){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('name','SchemeGroup'));
		}

		if($crud->isEditing()){
			$o->now();
			$f1 = $crud->form->getElement('isDepriciable');
		    $f1->js(true)->univ()->bindConditionalShow(array(
					''=>array(''),
					'*'=>array('DepriciationPercentBeforeSep','DepriciationPercentAfterSep')
					),'div .atk-form-row');
		}

		
	}

}