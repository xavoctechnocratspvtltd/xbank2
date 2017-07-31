<?php

class page_reports_loan_dealerstatement extends Page {
	public $title="Dealer Statement Repots";
	function page_index(){
		// parent::init();


		$from_date='1970-02-01';
		$to_date=$this->api->today;

		if($this->api->stickyGET('to_date')){
			$to_date = $this->api->stickyGET('to_date');
		}

		if($_GET['from_date']){
			$from_date = $this->api->stickyGET('from_date');
		}

		
		$form=$this->add('Form');
		$dealer=$form->addField('autocomplete/Basic','dealer')->validateNotNull();
		$dealer->setModel('Model_Dealer');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$loan_type=$form->addField('DropDown','loan_type');
		$array_value = $array_key = explode(',',LOAN_TYPES);
		$loan_type->setValueList(array_combine($array_key, $array_value))->setEmptyText('Select Account type');

		$form->addField('DropDown','status')->setValueList(array(1=>'Active',0=>'Inactive'));
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase',array('from_date'=>$from_date,'to_date'=>$to_date,'account_type'=>$_GET['account_type'],'account_status'=>$_GET['status']));
		$grid->add('H3',null,'grid_buttons')->set('Dealer Payment Detail From Date - '.$from_date.' To Date - '.$to_date);
		$grid->addSno();
		
		// $account_model->add('Controller_Acl');
		// $grid->addColumn('loan_amount');
		// $grid->addColumn('net_amount');
		// $grid->addColumn('file_charge');
		// $grid->addColumn('bank_detail');
		
		$account_model = $this->add('Model_Account');
		$dealer_id = -1;
		if($_GET['dealer']){
			$dealer_id = $this->api->stickyGET('dealer');
		}

		if($_GET['loan_type']){
			$account_type_value = $this->api->stickyGET('loan_type');
		}

		$account_model->addCondition('dealer_id',$dealer_id);

		$grid_column_array = array('AccountNumber','created_at','scheme','name','member_name_only','FatherName','CurrentAddress','PhoneNos','dealer_id','ActiveStatus');
		if($this->api->stickyGET('filter')){
			$account_model->addCondition('created_at','>=',$from_date);
			$account_model->addCondition('created_at','<=',$this->api->nextDate($to_date));
			
			if(isset($account_type_value)){
				$account_model->addCondition('SchemeType','Loan');
				$account_model->addCondition('account_type',$account_type_value);
			}

			$status = 1;
			if($_GET['status'] == 0){
				$account_model->addCondition('ActiveStatus','<>',1);
			 }else{
				$status = $this->api->stickyGET('status');
				$account_model->addCondition('ActiveStatus',$status);
			 }


			$member_join = $account_model->leftJoin('members','member_id');
			$member_join->addField('FatherName')->caption('Father/Husband Name');
			$member_join->addField('CurrentAddress');
			$member_join->addField('PhoneNos');


			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
					if(!isset($gridOrder)) $gridOrder = $grid->addOrder();
					$gridOrder->move($this->api->normalizeName($document['name']),'last');
				}
			}
		}

		$account_model->addExpression('file_charge')->set(function($m,$q){
			$s = $m->add('Model_Scheme',array('table_alias'=>'fcs'));
			$s->addCondition('id',$q->getField('scheme_id'));
			// return "'123'";
			return $q->expr("if([0]=1,[1]/100.0*[2],[2])",array($s->fieldQuery('ProcessingFeesinPercent'),$m->getElement('Amount'),$s->fieldQuery('ProcessingFees')));
		});

		$account_model->addExpression('sm_amount')->set(function($m,$q){
			$trans_m = $this->add('Model_TransactionRow');
			$trans_m->addCondition('reference_id',$q->getField('id'));
			$trans_m->addCondition('transaction_type',TRA_LOAN_ACCOUNT_OPEN);
			$trans_m->addCondition('scheme','SHARE CAPITAL');
			return $trans_m->fieldQuery('amountCr');
		});

		$account_model->addExpression('cheque_amount')->set(function($m,$q){
			return $q->expr("[0]-([1]+IFNULL([2],0))",array($m->getElement('Amount'),$m->getElement('file_charge'),$m->getElement('sm_amount')));
		});		

		$grid_column_array[] ='file_charge';
		$grid_column_array[] ='cheque_amount';
		$grid_column_array[] ='Amount';

		$grid->setModel($account_model,$grid_column_array);
		if(isset($gridOrder)) $gridOrder->now();
		// $move fileds at and 
		$grid->addTotals(array('Amount','file_charge','cheque_amount'));
		$grid->removeColumn('name');
		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'status'=>$form['status'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'loan_type'=>$form['loan_type'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();
		}

	}

}
