<?php

class page_reports_general_closingbalanceofaccount extends Page {
	public $title="Closing Balance of Account Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		$selected_account_type = $this->api->stickyGET('account_type');
		$filter = $this->api->stickyGET('filter');
		$this->api->stickyGET('status');
		$this->api->stickyGET('matured_status');

		if($_GET['as_on_date']){
			$this->api->stickyGET('as_on_date');
			$till_date=$this->api->nextDate($_GET['as_on_date']);
		}

		$form=$this->add('Form');
		$form->addField('DatePicker','as_on_date');
		$form->addField('DropDown','status')->setValueList(array('Active'=>'Active','InActive'=>'InActive'))->setEmptyText('Select Status');
		$account_type=$form->addField('DropDown','account_type');
		$array_value = $array_key = explode(',', ACCOUNT_TYPES);
		$account_type->setValueList(array_combine($array_key, $array_value))->setEmptyText('Select Account type');

		$form->addField('DropDown','matured_status')->setValueList(array('Active'=>'Active','InActive'=>'InActive'))->setEmptyText('Select Status');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_Report_ClosingBalanceOfAccount',array('as_on_date'=>$till_date));
		$grid->add('H3',null,'grid_buttons')->set('Account Close Report As On '. date('d-M-Y',strtotime($till_date)));

		// $account_model = $this->add('Model_Active_Account');
		
		$tr_model = $this->add('Model_TransactionRow');
		// $tr_model->addCondition('branch_id',$this->api->current_branch->id);

		$tr_account_join = $tr_model->join('accounts','account_id');
		$tr_account_join->addField('member_id');
		$tr_account_join->addField('AccountNumber');
		$tr_account_join->addField('DefaultAC');
		$tr_account_join->addField('OpeningBalanceDr');
		$tr_account_join->addField('OpeningBalanceCr');
		$tr_account_join->addField('ActiveStatus');
		$tr_account_join->addField('acc_created_at','created_at');
		$tr_account_join->addField('MaturedStatus','MaturedStatus');

		$scheme_join = $tr_account_join->leftJoin('schemes','scheme_id');
		$scheme_join->addField('SchemeType');
		$scheme_join->addField('scheme_name','name');

		$member_join = $tr_account_join->Join('members','member_id');
		$member_join->addField('FatherName')->caption('Father/Husband Name');
		$member_join->addField('PermanentAddress');
		$member_join->addField('name');
		$member_join->addField('PhoneNos');
		
		$tr_model->addExpression('sum')->set(function($m,$q){
			return $m->dsql()->expr('sum(amountCr - amountDr)'); //$m->getElement('amountCr') - $m->getElement('amountDr');
		});

		$tr_model->addExpression('sm_no')->set(function($m,$q){
			$acc = $this->add('Model_Account',['table_alias'=>'sm_no']);
			return $acc->addCondition('member_id',$m->getField('member_id'))->addCondition('SchemeType','Default')->addCondition('scheme_name','Share Capital')->setLimit(1)->fieldQuery('AccountNumber');
		});

		$fields_array=array('AccountNumber','acc_created_at','name','FatherName','PermanentAddress','PhoneNos','scheme_name','SchemeType','sum','OpeningBalanceDr','OpeningBalanceCr','member_id','member','MaturedStatus');

		if($filter){
			if($selected_account_type){
				$tr_model->addCondition('SchemeType',$selected_account_type);
			}
			if($_GET['as_on_date'])
				$tr_model->addCondition('created_at','<',$this->api->nextDate($_GET['as_on_date']));
			
			if($_GET['status']=='Active'){
				$tr_model->addCondition('ActiveStatus',true);
			}else{
				$tr_model->addCondition('ActiveStatus',false);
			}

			if($selected_account_type == 'SavingAndCurrent'){
				$fields_array[]='sm_no';
			}

			if($_GET['matured_status'] == "Active"){
				$tr_model->addCondition('MaturedStatus',1);	
			}elseif($_GET['matured_status'] == "InActive"){
				$tr_model->addCondition('MaturedStatus',0);
			}

		}else{
			$tr_model->addCondition('id',-1);
		}
		
		$tr_model->addCondition('DefaultAC',0);
		
		$tr_model->_dsql()->group('account_id');

		$tr_model->setOrder('account_id');
		$tr_model->add('Controller_Acl');
		$grid->setModel($tr_model,$fields_array);
			
		$grid->addPaginator(10);

		if($form->isSubmitted()){
			$grid->js()->reload(
								array('as_on_date'=>$form['as_on_date']?:0,
									'account_type'=>$form['account_type'],
									'status'=>$form['status'],
									'matured_status'=>$form['matured_status'],
									'filter'=>1
								))->execute();
		}

	}

}
