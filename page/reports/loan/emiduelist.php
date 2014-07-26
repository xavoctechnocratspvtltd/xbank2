<?php

class page_reports_loan_emiduelist extends Page {
	public $title="EMI Due List (Recovery List)";
	function init(){
		parent::init();

		$form=$this->add('Form');
		$grid=$this->add('Grid'); 
		$till_date = $this->api->today;

		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}
		$grid->add('H3',null,'grid_buttons')->set('EMI Due List As On '. date('d-M-Y',strtotime($till_date)));
		$grid_column_array = array('dealer','AccountNumber','created_at','scheme','member_name','FatherName','PhoneNos','PermanentAddress','paid_premium_count','due_premium_count','emi_amount','due_panelty','other_charges','guarantor_name','last_premium');

		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		// $form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date','As On');
		$form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','hardlist'=>'Hard List','npa'=>'NPA List','time_collapse'=>'Time Collapse'));
		$form->addField('dropdown','loan_type')->setValueList(array('all'=>'All','vl'=>'VL','pl'=>'PL','other'=>'Other'));
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}
		$form->addSubmit('GET List');


		$account_model=$this->add('Model_Active_Account_Loan');
		$member_join=$account_model->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');

		$account_model->addCondition('DefaultAC',false);
		// $account_model->addCondition('MaturedStatus',false); //???

		$account_model->addExpression('paid_premium_count')->set(function($m,$q){
			return $m->refSQL('Premium')
						->addCondition('PaidOn','<>',null)
						// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
						->addCondition('DueDate','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today))
						->count();
		})->sortable(true);

		$account_model->addExpression('due_premium_count')->set(function($m,$q){
			return $m->refSQL('Premium')
						->addCondition('PaidOn',null)
						// ->addCondition('DueDate','>',$_GET['from_date']?:'1970-01-01')
						->addCondition('DueDate','<=',$_GET['to_date']?$m->api->nextDate($_GET['to_date']):$m->api->nextDate($m->api->today))
						->count();
		});

		$account_model->addExpression('last_premium')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('DueDate');
		});

		$account_model->addExpression('emi_amount')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('Amount');
		});

		$account_model->addExpression('due_panelty')->set(function($m,$q){
			return $m->refSQL('Premium')->addCondition('PaneltyCharged','<>',$m->api->db->dsql()->expr('PaneltyPosted'))->addCondition('DueDate','<',$_GET['to_date']?:$m->api->today)->sum($m->dsql()->expr('PaneltyCharged - PaneltyPosted'));
		});

		$account_model->addExpression('other_charges')->set(function($m,$q){
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
			$tr_m->addCondition('transaction_type_id',13); // JV
			$tr_m->addCondition('account_id',$q->getField('id'));
			return $tr_m->sum('amountDr');

		});

		$account_model->addExpression('guarantor_name')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.name');
		});
		

		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('dealer');
			$this->api->stickyGET('report_type');
			// $this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			$this->api->stickyGET('loan_type');
			$this->api->stickyGET('report_type');

			if($_GET['dealer'])
				$account_model->addCondition('dealer_id',$_GET['dealer']);

			switch ($_GET['report_type']) {
				case 'duelist':
					$account_model->addCondition('due_premium_count','>',0);
					$account_model->addCondition('due_premium_count','<=',2);
					$account_model->addCondition('last_premium','<',$this->api->today);
					break;
				case 'hardlist':
					$account_model->addCondition('due_premium_count','>',2);
					$account_model->addCondition('due_premium_count','<=',5);
					$account_model->addCondition('last_premium','<',$this->api->today);
					break;
				case 'npa':
					$account_model->addCondition('due_premium_count','>',5);
					$account_model->addCondition('last_premium','<',$this->api->today);
					break;

				case 'time_collapse':
					$account_model->addCondition('last_premium','>',$this->api->today);
					break;
				
				default:
					# code...
					break;
			}

			switch ($_GET['loan_type']) {
				case 'vl':
					$account_model->addCondition('AccountNumber','like','%vl%');
					break;
				case 'pl':
					$account_model->addCondition('AccountNumber','like','%pl%');
					break;
				case 'other':
					$account_model->addCondition('AccountNumber','not like','%pl%');
					$account_model->addCondition('AccountNumber','not like','%vl%');
					// $account_model->_dsql()->where('(accounts.AccountNumber not like "%pl%" and accounts.AccountNumber not like "%pl%")');
					break;
			}

			foreach ($document as $junk) {
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($document){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$document->id)->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}

		}

		$account_model->add('Controller_Acl');
		$grid->setModel($account_model,$grid_column_array);
		$grid->addPaginator(50);

		$grid->removeColumn('last_premium');

		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],/*'from_date'=>$form['from_date']?:0,*/'to_date'=>$form['to_date']?:0,'report_type'=>$form['report_type'], 'loan_type'=>$form['loan_type'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}		


	}
}