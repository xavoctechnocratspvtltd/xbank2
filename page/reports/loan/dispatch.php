<?php

class page_reports_loan_dispatch extends Page {
	public $title="Loan Dispatch Report";
	public $setdealer = 0;
	function init(){
		parent::init();


		$till_date="";
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		if($this->app->stickyGET('setdealer')){
			$this->setdealer = $_GET['setdealer'];
		}

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');
		if($this->setdealer){
			$dealer_field->getModel()->addCondition('id',$this->setdealer);
			$dealer_field->set($this->setdealer);
			$dealer_field->validateNotNull();
		}

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addField('dropdown','loan_type')->setValueList(array('all'=>'All','vl'=>'VL','pl'=>'PL','fvl'=>'FVL','sl'=>'SL','hl'=>'HL','other'=>'Other'));
		$form->addField('dropdown','dsa')->setEmptyText('All DSA')->setModel('DSA');

		// $form->addField('DropDown','document')->setEmptyText('No Document')->setModel('Document')->addCondition('LoanAccount',true);
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->addSno(); 
		$grid->add('H3',null,'grid_buttons')->set('Loan Dispatch Report As On '. date('d-M-Y',strtotime($till_date))); 

		$account_model=$this->add('Model_Account_Loan');

		$member_join = $account_model->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('CurrentAddress');
		$member_join->addField('PhoneNos');

		$account_model->addExpression('member_sm')->set(function($m,$q){
			return $acc = $this->add('Model_Account_SM',['table_alias'=>'mem_sm'])->addCondition('member_id',$q->getField('member_id'))->setLimit(1)->fieldQuery('AccountNumber');
		});

		$account_model->addExpression('guarantor_sm')->set(function($m,$q){
			$guarantor_m = $m->add('Model_AccountGuarantor',array('table_alias'=>'g_sm'));
			// $ac_join = $guarantor_m->join('account_guarantors.account_id');
			// $ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');

			return $acc = $this->add('Model_Account_SM',['table_alias'=>'mem_sm'])->addCondition('member_id',$guarantor_m->fieldQuery('member_id'))->setLimit(1)->fieldQuery('AccountNumber');
		});
		
		$account_model->addExpression('no_of_emi')->set(function($m,$q){
			return $m->refSQL('Premium')->count();
		});

		$account_model->addExpression('emi')->set(function($m,$q){
			return $m->refSQL('Premium')->setLimit(1)->fieldQuery('Amount');
		});


		$account_model->addExpression('guarantor_name')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id','desc');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.name');
		});


		$account_model->addExpression('guarantor_phno')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.PhoneNos');
		});

		$account_model->addExpression('guarantor_fathername')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.FatherName');
		});


		$account_model->addExpression('guarantor_addres')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.PermanentAddress');
		});

		$account_model->addExpression('dsa_id')->set(function($m,$q){
			return $m->refSQL('dealer_id')->fieldQuery('dsa_id');
		});

		$grid_array = array('AccountNumber','LoanAgainst','created_at','member','member_sm','FatherName','CurrentAddress','scheme','PhoneNos','guarantor_name','guarantor_sm','guarantor_fathername','guarantor_phno','guarantor_addres','Amount','file_charge','gst_amount','insurance_processing_fees_amount','cheque_amount','no_of_emi','emi');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			
			if($doc_id = $_GET['document']){
				$this->api->stickyGET('document');

				$document_model = $this->add('Model_Document')->load($_GET['document']);

				$account_model->addExpression($this->api->normalizeName($document_model['name']))->set(function($m,$q)use($doc_id){
					return $m->refSQL('DocumentSubmitted')
							->addCondition('documents_id',$doc_id)
							->addCondition('accounts_id',$q->getField('id'))
							->fieldQuery('Description');
				});
				$grid_array[] = $this->api->normalizeName($document_model['name']);
				
			}

			if($_GET['dealer']){
				$this->api->stickyGET('dealer');
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			if($_GET['from_date']){
				$this->api->stickyGET('from_date');
				$account_model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET('to_date');
				$account_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

			switch ($this->app->stickyGET('loan_type')) {
				case 'vl':
					$account_model->addCondition('AccountNumber','like','%vl%');
					$account_model->addCondition('AccountNumber','not like','%fvl%');
					break;
				case 'pl':
					$account_model->addCondition('AccountNumber','like','%pl%');
					break;
				case 'fvl':
					$account_model->addCondition('AccountNumber','like','%FVL%');
					break;
				case 'sl':
					$account_model->addCondition('AccountNumber','like','%SL%');
					$account_model->addCondition('AccountNumber','not like','%VL%');
					break;
				case 'hl':
					$account_model->addCondition('AccountNumber','like','%HL%');
					break;
				case 'other':
					$account_model->addCondition('AccountNumber','not like','%pl%');
					$account_model->addCondition('AccountNumber','not like','%vl%');
					$account_model->addCondition('AccountNumber','not like','%hl%');
					// $account_model->_dsql()->where('(accounts.AccountNumber not like "%pl%" and accounts.AccountNumber not like "%pl%")');
					break;
			}

			if($this->app->stickyGET('dsa')){
				$account_model->addCondition('dsa_id',$_GET['dsa']);
				if(!$_GET['dealer']) $grid_array[] ='dealer';
			}


			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_array[] = $this->api->normalizeName($document['name']);
				}
			}
		}
		else
			$account_model->addCondition('id',-1);

		$account_model->addCondition('DefaultAC',false);

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
			return $trans_m->sum('amountCr');
		});

		$account_model->addExpression('gst_amount')->set(function($m,$q){
			$trans_m = $this->add('Model_TransactionRow');
			$trans_m->addCondition('reference_id',$q->getField('id'));
			$trans_m->addCondition('transaction_type',TRA_LOAN_ACCOUNT_OPEN);
			$trans_m->addCondition('scheme','Duties Taxes');
			return $q->expr('IFNULL([0],0)',[$trans_m->sum('amountCr')]);
		});

		$account_model->addExpression('insurance_processing_fees_amount')->set(function($m,$q){
			$trans_m = $this->add('Model_TransactionRow');
			$trans_m->addCondition('reference_id',$q->getField('id'));
			$trans_m->addCondition('transaction_type',TRA_LOAN_ACCOUNT_OPEN);
			$trans_m->addCondition('account','like','% INSURANCE PROCESSING FEES%');
			return $q->expr('IFNULL([0],0)',[$trans_m->sum('amountCr')]);
		});




		$account_model->addExpression('cheque_amount')->set(function($m,$q){
			return $q->expr("[0]-([1]+IFNULL([2],0) + IFNULL([3],0)+IFNULL([4],0) )",array($m->getElement('Amount'),$m->getElement('file_charge'),$m->getElement('sm_amount'),$m->getElement('gst_amount'),$m->getElement('insurance_processing_fees_amount')));
		});

		$account_model->addExpression('LoanAgainst')->set(function($m,$q){
			$x = $m->add('Model_Account',['table_alias'=>'loan_ag']);
			return $x->addCondition('id',$q->getField('LoanAgainstAccount_id'))->fieldQuery('AccountNumber');
		});

		$grid->setModel($account_model,$grid_array);
		
		$grid->addMethod('format_myTotal',function($grid, $field){
			$grid->current_row[$field] = $grid->current_row['no_of_emi'] * $grid->current_row['emi'];
		});

		$grid->addColumn('myTotal','total');

		$order=$grid->addOrder();//->move('deposit','before','dr_sum')->now();
		// $order->move('file_charge','after','Amount')->now();
		// $order->move('cheque_amount','after','file_charge')->now();

		$grid->addPaginator(500);

		$grid->addTotals(array('total','Amount','file_charge','cheque_amount','emi'));

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);
		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'document'=>$form['document']?:0,'loan_type'=>$form['loan_type'], 'dsa'=>$form['dsa'], 'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();
		}		


	}
}