<?php

class page_reports_general_closingbalanceofaccount extends Page {
	public $title="Closing Balance of Account Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		$selected_account_type = $this->api->stickyGET('account_type');
		$filter = $this->api->stickyGET('filter');
		$this->api->stickyGET('status');

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
		}
		
		$tr_model->addCondition('DefaultAC',0);
		
		$tr_model->_dsql()->group('account_id');

		$tr_model->setOrder('account_id');
		$tr_model->add('Controller_Acl');
		$grid->setModel($tr_model,array('AccountNumber','name','FatherName','PermanentAddress','PhoneNos','scheme_name','SchemeType','sum','OpeningBalanceDr','OpeningBalanceCr','member_id','member'));
		
		if($form->isSubmitted()){
			$grid->js()->reload(
								array('as_on_date'=>$form['as_on_date']?:0,
									'account_type'=>$form['account_type'],
									'status'=>$form['status'],
									'filter'=>1))->execute();
		}

	}

}
