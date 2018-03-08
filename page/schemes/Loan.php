<?php

class page_schemes_Loan extends Page{
	function page_index(){
		// parent::init();

		$crud=$this->add('xCRUD',array('grid_class'=>'Grid_Scheme'));
		$scheme_Loan_model =$this->add('Model_Scheme_Loan');
		$scheme_Loan_model->setOrder('id','desc');
		
		$crud->addHook('myupdate',function($crud,$form){
			if($crud->isEditing('edit')) return false;
						
			$Loan_scheme_model = $crud->add('Model_Scheme_Loan');
			try {
				$form->api->db->beginTransaction();
			    $Loan_scheme_model->createNewScheme($form['name'],$form['balance_sheet_id'], ACCOUNT_TYPE_LOAN, ACCOUNT_TYPE_LOAN, $is_loanType=$form['type'], $other_values=$form->getAllFields(),$form,$form->api->now);
			    $form->api->db->commit();
			} catch (Exception $e) {
			   	$form->api->db->rollBack();
			   	throw $e;
			}
			return true;
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
		    // $t=array('Two Wheeler Loan','Auto Loan','Personal Loan','Loan Against Deposit','Home Loan','Mortgage Loan','Agriculture Loan','Education Loan','Gold Loan','Other');
		    // $crud->form->addField('DropDown','loan_type')->setEmptyText('Please select')->setValueList(array_combine($t,$t))->validateNotNull();
		}

		if($crud->isEditing('edit')){
			$scheme_Loan_model->hook('editing');
			// $scheme_Loan_model->getElement('type')->system(true);
		}

		$crud->setModel($scheme_Loan_model,array('type','name','Interest','ReducingOrFlatRate','PremiumMode','NumberOfPremiums','ActiveStatus','balance_sheet','balance_sheet_id','ProcessingFees','ProcessingFeesinPercent','SchemeGroup','MinLimit','MaxLimit','total_accounts','total_active_accounts','valid_till','panelty','panelty_grace'));

		
		if(!$crud->isEditing()){
			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('name'));
		}

		if($crud->isEditing('add')){
			$o->now();
		}
		
	}
}