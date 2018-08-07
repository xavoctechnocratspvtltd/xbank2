<?php

class page_reports_loan_dealerwise extends Page {

	public $title="EMI Due List Dealer Wise (Recovery List)";

	function init(){
		parent::init();

		$form=$this->add('Form');
		$grid=$this->add('Grid_AccountsBase'); 
		$from_date = null;
		$to_date = $this->api->today;

		if($_GET['to_date']){
			$to_date=$this->api->stickyGET('to_date');
		}

		if($_GET['from_date']){
			$from_date=$this->api->stickyGET('from_date');
		}

		if(!$from_date && $to_date){
			$grid->add('H3',null,'grid_buttons')->set('Loan EMI '.$_GET['report_type'].' As On '. date('d-M-Y',strtotime($to_date)));
		}

		if($from_date && $to_date){
			$grid->add('H3',null,'grid_buttons')->set('Loan EMI '.$_GET['report_type'].' From '.date('d-M-Y',strtotime($from_date)).' To '. date('d-M-Y',strtotime($to_date)));
		}

		// $dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		// $dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','hardlist'=>'Hard List','npa'=>'NPA List','time_collapse'=>'Time Collapse'));
		$form->addField('dropdown','loan_type')->setValueList(array('all'=>'All','vl'=>'VL','pl'=>'PL','fvl'=>'FVL','sl'=>'SL','hl'=>'HL','other'=>'Other'));
		$form->addField('dropdown','dsa')->setEmptyText('All DSA')->setModel('DSA');
		$form->addField('dropdown','bike_surrendered')->setValueList(['include'=>'Include / All','exclude'=>'Exclude','only'=>'Only']);
		$form->addField('dropdown','legal_accounts')->setValueList(['include'=>'Include / All','exclude'=>'Exclude','only'=>'Only']);
		$form->add('HR');

		$document=$this->add('Model_Document');
		// $document->addCondition('LoanAccount',true);
		// foreach ($document as $junk) {
		// 	$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		// }
		$form->addSubmit('GET List');


		$account_model=$this->add('Model_Active_Account_Loan');
		$q=$account_model->dsql();

		$member_join=$account_model->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('CurrentAddress');
		$member_join->addField('landmark');

		$account_model->addCondition('DefaultAC',false);

		// $account_model_j=$account_model->join('premiums.account_id','id');
		// $account_model_j->addField('DueDate');
		// $account_model->addCondition('MaturedStatus',false); //???

		$grid_column_array = array('AccountNumber','created_at','maturity_date','due_date','scheme','member_name','FatherName','CurrentAddress','landmark','PhoneNos','dealer','guarantor_name','guarantor_phno','guarantor_address','last_premium','paid_premium_count','due_premium_count','emi_amount','emi_dueamount','due_panelty','other_charges','other_received','total');
		
		$account_model->addExpression('paid_premium_count')->set(function($m,$q)use($from_date,$to_date){
			$p_m=$m->refSQL('Premium')
						->addCondition('PaidOn','<>',null);
			if($from_date)
				$p_m->addCondition('DueDate','>=',$from_date);
			if($to_date)
				$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->count();
		})->sortable(true);

		$account_model->addExpression('due_premium_count')->set(function($m,$q)use($from_date, $to_date){
			$p_m = $m->refSQL('Premium')
						->addCondition('PaidOn',null);
			if($from_date)
				$p_m->addCondition('DueDate','>=',$from_date);
			if($to_date)
				$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->count();
		});

		$account_model->addExpression('due_date')->set(function($m,$q){
			$t = $m->refSQL('Premium')->setLimit(1);
			return $q->expr("DAY([0])",array($t->fieldQuery('DueDate')));
			return "'due_premium_count'";
		});

		$account_model->addExpression('last_premium')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('DueDate');
			return "'last_premium'";
		});

		$account_model->addExpression('emi_amount')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('Amount');
			return "'emi_amount'";
		});

		$account_model->addExpression('due_panelty')->set(function($m,$q)use($from_date,$to_date){
			$trans_type = $this->add('Model_TransactionType')->tryLoadBy('name',TRA_PENALTY_ACCOUNT_AMOUNT_DEPOSIT);
			
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'due_panelty_tr'));
			$tr_m->addCondition('transaction_type_id',$trans_type->id); 
			$tr_m->addCondition('account_id',$q->getField('id'));
			$tr_m->addCondition('created_at','>=',$from_date);
			$tr_m->addCondition('created_at','<',$this->app->nextDate($to_date));
			
			return $tr_m->sum('amountDr');

			// Previously this was running, and was including un entered amount also, but
			// this was changed as per request ... 
			// Reason, old accounts was not included in penalty
			$p_m = $m->refSQL('Premium');
			if($from_date)
				$p_m->addCondition('DueDate','>=',$from_date);
			if($to_date)
				$p_m->addCondition('DueDate','<',$m->api->nextDate($to_date));
			return $p_m->sum($m->dsql()->expr('IFNULL(PaneltyCharged,0)'));
		});

		$account_model->addExpression('other_charges')->set(function($m,$q){
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
			$tr_m->addCondition('transaction_type_id',[13, 46, 39]); // JV, TRA_VISIT_CHARGE, LegalChargeReceived, TRA_PENALTY_AMOUNT_RECEIVED
			$tr_m->addCondition('account_id',$q->getField('id'));
			return $tr_m->sum('amountDr');
		});

		$account_model->addExpression('other_received')->set(function($m,$q){
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
			$tr_m->addCondition('account_id',$q->getField('id'));
			$received = $tr_m->sum('amountCr');
			$premium_paid = $q->expr('([0]*[1])',[$m->getElement('paid_premium_count'),$m->getElement('emi_amount')]);
			return $q->expr('([0]-[1])',[$received,$premium_paid]);
		});

		$account_model->addExpression('guarantor_name')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.name');
			return "'guarantor_name'";
		});


		$account_model->addExpression('guarantor_phno')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.PhoneNos');
			return "'guarantor_phno'";
		});

		$account_model->addExpression('guarantor_address')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_addr_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.PermanentAddress');
			return "'guarantor_phno'";
		});

		$account_model->addExpression('dsa_id')->set(function($m,$q){
			return $m->refSQL('dealer_id')->fieldQuery('dsa_id');
		});
		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('dealer');
			$this->api->stickyGET('report_type');
			$this->api->stickyGET('to_date');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('loan_type');
			$this->api->stickyGET('report_type');
			$this->api->stickyGET('bike_surrendered');
			$this->api->stickyGET('legal_accounts');


			// $account_model->addCondition('DueDate','<=',$till_date);

			// if($_GET['dealer'])
			// 	$account_model->addCondition('dealer_id',$_GET['dealer']);

			// done again as per devendra sir bu looking at emidue list condition 
			// if($_GET['bike_surrendered']==='include' AND $_GET['legal_accounts']==='include'){
				switch ($_GET['report_type']) {
					case 'duelist':
						$account_model->addCondition('due_premium_count','>',0);
						$account_model->addCondition('due_premium_count','<=',2);
						$account_model->addCondition('last_premium','>=',$to_date);
						break;
					case 'hardlist':
						$account_model->addCondition('due_premium_count','>',2);
						$account_model->addCondition('due_premium_count','<=',4);
						$account_model->addCondition('last_premium','>=',$to_date);
						break;
					case 'npa':
						$account_model->addCondition('due_premium_count','>=',5);
						$account_model->addCondition('last_premium','>=',$to_date);
						break;

					case 'time_collapse':
						$account_model->addCondition($account_model->dsql()->expr('[0] < "[1]"',array($account_model->getElement('last_premium'),$to_date)));
						break;
					
					default:
						# code...
						break;
				}
			// }

			switch ($_GET['loan_type']) {
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
					break;
				case 'hl':
					$account_model->addCondition('AccountNumber','like','%HL%');
					break;
				case 'other':
					$account_model->addCondition('AccountNumber','not like','%pl%');
					$account_model->addCondition('AccountNumber','not like','%vl%');
					// $account_model->_dsql()->where('(accounts.AccountNumber not like "%pl%" and accounts.AccountNumber not like "%pl%")');
					break;
			}

			if($this->app->stickyGET('dsa')){
				$account_model->addCondition('dsa_id',$_GET['dsa']);
				if(!$_GET['dealer']) $grid_column_array[] ='dealer';
			}

			switch ($_GET['bike_surrendered']) {
				case 'only':
					$account_model->addCondition('bike_surrendered',true);
					break;
				case 'exclude':
					$account_model->addCondition('bike_surrendered',true);
					$account_model->addCondition('is_bike_returned',false);
					$account_model->addCondition('is_given_for_legal_process',false);
					break;
				case 'include':
				default:
					break;
			}

			switch ($_GET['legal_accounts']) {
				case 'only':
					$account_model->addCondition('is_given_for_legal_process',true);
					break;
				case 'exclude':
					$account_model->addCondition('is_given_for_legal_process',false);
					break;
				case 'include':
				default:
					break;
			}

			// $grid->addMethod('format_total',function($g,$f){
			// 	$temp  = $g->current_row_html[$f]= ($g->model['due_premium_count'] * $g->model['emi_amount']) +$g->model['due_panelty']+$g->model['other_charges'];
			// 	if(!isset($g->total)) $g->total=0;
			// 	$g->total += $temp;
			// });

			// $grid->addMethod('format_totals_total',function($g,$f){
			// 	$g->current_row_html[$f]= $g->total;
			// });

			// $grid->addMethod('format_emidue',function($g,$f){

			// 	$temp  = $g->current_row_html[$f]=$g->model['due_premium_count']*$g->model['emi_amount'];
			// 	if(!isset($g->emidue)) $g->emidue=0;
			// 	$g->emidue += $temp;

			// });

			// $grid->addMethod('format_totals_emidue',function($g,$f){

			// 	$g->current_row_html[$f]= $g->emidue;

			// });


			// foreach ($document as $junk) {
			// 	$doc_id = $document->id;
			// 	if($_GET['doc_'.$document->id]){
			// 		$this->api->stickyGET('doc_'.$document->id);
			// 		$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
			// 			return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
			// 		});
			// 		$grid_column_array[] = $this->api->normalizeName($document['name']);
			// 	}
			// }

		}else
			$account_model->addCondition('id',-1);

		$account_model->addExpression('emi_dueamount')->set(function($m,$q){
			return $q->expr('([0]*[1])',[$m->getElement('due_premium_count'),$m->getElement('emi_amount')]);
		});


		$account_model->addExpression('sum_emi_amount')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('emi_amount')]);
		});

		$account_model->addExpression('sum_emi_due_amount')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('emi_dueamount')]);
		});

		$account_model->addExpression('sum_due_panelty')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('due_panelty')]);
		});

		$account_model->addExpression('sum_other_charges')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('other_charges')]);
		});

		$account_model->addExpression('sum_other_received')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('other_received')]);
		});

		$account_model->addExpression('total')->set(function($m,$q){
			return $q->expr('IFNULL([sum_emi_due_amount],0) + IFNULL([sum_due_panelty],0) + IFNULL([sum_other_charges],0) - IFNULL([sum_other_received],0)',
			[
				'sum_emi_due_amount' => $m->getElement('sum_emi_due_amount'),
				'sum_due_panelty' 	=> $m->getElement('sum_due_panelty'),
				'sum_other_charges'	=> $m->getElement('sum_other_charges'),
				'sum_other_received'	=> $m->getElement('sum_other_received')
			]);//($m['due_premium_count'] * $m['emi_amount']) +$m['due_panelty']+$m['other_charges']-$m['other_received'];
		});

		$account_model->addExpression('count_accounts')->set('count(*)');

		$account_model->_dsql()->group($account_model->dsql()->expr('[0]',[$account_model->getElement('dealer_id')]));



		// $account_model->_dsql()->group('id');
		$account_model->add('Controller_Acl');

		$grid->setModel($account_model,['dealer','count_accounts','sum_emi_amount','sum_emi_due_amount','sum_due_panelty','sum_other_charges','sum_other_received','total']);

		if($_GET['filter']){
			// $grid->addColumn('emidue','emi_dueamount');
			// $grid->addColumn('total','total');
			// $grid->addOrder()
			// 	->move('emi_dueamount','after','emi_amount')
			// 	->move('total','after','other_received')
			// 	->now();

			// $grid->addFormatter('guarantor_address','wrap');
			// $grid->addFormatter('CurrentAddress','wrap');
		}
		// $grid->addColumn('text','openning_date');

		$grid->addPaginator(500);
		$grid->addSno();
		$grid->addTotals(array('total','sum_emi_due_amount','sum_due_panelty','sum_other_charges','sum_other_received'));
		$grid->add('Controller_xExport',array('fields'=>array_merge($grid_column_array,array('emi_dueamount','total')),'totals'=>array('total','emi_dueamount','other_charges','emi_amount','due_panelty') ,'output_filename'=>$_GET['report_type'].' lilst_as_on '. $to_date.".csv"));

		$grid->removeColumn('last_premium');
		// $js=array(
		// 	// $this->js()->_selector('.atk-layout-row')->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){

			$send = array('dealer'=>$form['dealer'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'report_type'=>$form['report_type'], 'loan_type'=>$form['loan_type'], 'dsa'=>$form['dsa'], 'filter'=>1 ,'legal_accounts'=>$form['legal_accounts'],'bike_surrendered'=>$form['bike_surrendered']);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}		


	}
}