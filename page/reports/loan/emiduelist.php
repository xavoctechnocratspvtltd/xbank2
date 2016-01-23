<?php

class page_reports_loan_emiduelist extends Page {

	public $title="EMI Due List (Recovery List)";

	function init(){
		parent::init();

		$form=$this->add('Form');
		$grid=$this->add('Grid_AccountsBase'); 
		$till_date = $this->api->today;

		if($_GET['till_date']){
			$till_date=$this->api->stickyGET('till_date');
		}
		$grid->add('H3',null,'grid_buttons')->set('Loan EMI '.$_GET['report_type'].' As On '. date('d-M-Y',strtotime($till_date)));

		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','till_date');
		$form->addField('dropdown','report_type')->setValueList(array('duelist'=>'Due List','hardlist'=>'Hard List','npa'=>'NPA List','time_collapse'=>'Time Collapse'));
		$form->addField('dropdown','loan_type')->setValueList(array('all'=>'All','vl'=>'VL','pl'=>'PL','fvl'=>'FVL','sl'=>'SL','other'=>'Other'));
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}
		$form->addSubmit('GET List');


		$account_model=$this->add('Model_Active_Account_Loan');
		$q=$account_model->dsql();

		$member_join=$account_model->join('members','member_id');
		// $member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');

		$account_model->addCondition('DefaultAC',false);

		// $account_model_j=$account_model->join('premiums.account_id','id');
		// $account_model_j->addField('DueDate');
		// $account_model->addCondition('MaturedStatus',false); //???

		$grid_column_array = array('AccountNumber','created_at','maturity_date','due_date','scheme','member','FatherName','PermanentAddress','PhoneNos','dealer','guarantor_name','guarantor_phno','guarantor_address','last_premium','paid_premium_count','due_premium_count','emi_amount','emi_dueamount','due_panelty','other_charges','total');
		
		$account_model->addExpression('paid_premium_count')->set(function($m,$q)use($till_date){
			return $m->refSQL('Premium')
						->addCondition('PaidOn','<>',null)
						->addCondition('DueDate','<',$m->api->nextDate($till_date))
						->count();
			return "'paid_premium_count'";
		})->sortable(true);

		$account_model->addExpression('due_premium_count')->set(function($m,$q)use($till_date){
			return $m->refSQL('Premium')
						->addCondition('PaidOn',null)
						->addCondition('DueDate','<',$m->api->nextDate($till_date))
						->count();
			return "'due_premium_count'";
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

		$account_model->addExpression('due_panelty')->set(function($m,$q)use($till_date){
			return $m->refSQL('Premium')->addCondition('DueDate','<',$till_date)->sum($m->dsql()->expr('IFNULL(PaneltyCharged,0)'));
			return "'due_panelty'";
		});

		$account_model->addExpression('other_charges')->set(function($m,$q){
			$tr_m = $m->add('Model_TransactionRow',array('table_alias'=>'other_charges_tr'));
			$tr_m->addCondition('transaction_type_id',13); // JV
			$tr_m->addCondition('account_id',$q->getField('id'));
			return $tr_m->sum('amountDr');
			return "'other_charges'";

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
		

		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('dealer');
			$this->api->stickyGET('report_type');
			$this->api->stickyGET('till_date');
			$this->api->stickyGET('loan_type');
			$this->api->stickyGET('report_type');


			// $account_model->addCondition('DueDate','<=',$till_date);

			if($_GET['dealer'])
				$account_model->addCondition('dealer_id',$_GET['dealer']);

			switch ($_GET['report_type']) {
				case 'duelist':
					$account_model->addCondition('due_premium_count','>',0);
					$account_model->addCondition('due_premium_count','<=',2);
					$account_model->addCondition('last_premium','>=',$till_date);
					break;
				case 'hardlist':
					$account_model->addCondition('due_premium_count','>',2);
					$account_model->addCondition('due_premium_count','<=',4);
					$account_model->addCondition('last_premium','>=',$till_date);
					break;
				case 'npa':
					$account_model->addCondition('due_premium_count','>=',5);
					$account_model->addCondition('last_premium','>=',$till_date);
					break;

				case 'time_collapse':
					$account_model->addCondition($account_model->dsql()->expr('[0] < "[1]"',array($account_model->getElement('last_premium'),$till_date)));
					break;
				
				default:
					# code...
					break;
			}

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
				case 'other':
					$account_model->addCondition('AccountNumber','not like','%pl%');
					$account_model->addCondition('AccountNumber','not like','%vl%');
					// $account_model->_dsql()->where('(accounts.AccountNumber not like "%pl%" and accounts.AccountNumber not like "%pl%")');
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


			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$account_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}

		}else
			$account_model->addCondition('id',-1);

		$account_model->add('misc\Field_Callback','total')->set(function($m){
			return ($m['due_premium_count'] * $m['emi_amount']) +$m['due_panelty']+$m['other_charges'];
		});

		$account_model->add('misc\Field_Callback','emi_dueamount')->set(function($m){
			return $m['due_premium_count']*$m['emi_amount'];
		});

		// $account_model->_dsql()->group('id');
		$account_model->add('Controller_Acl');




		$grid->setModel($account_model,$grid_column_array);

		if($_GET['filter']){
			// $grid->addColumn('emidue','emi_dueamount');
			// $grid->addColumn('total','total');
			$grid->addOrder()
				->move('emi_dueamount','after','emi_amount')
				->move('total','after','other_charges')
				->now();

			$grid->addFormatter('guarantor_address','wrap');
			$grid->addFormatter('PermanentAddress','wrap');
		}
		// $grid->addColumn('text','openning_date');

		$grid->addPaginator(500);
		$grid->addSno();
		$grid->addTotals(array('total','emi_dueamount','other_charges','emi_amount','due_panelty'));
		$grid->add('Controller_xExport',array('fields'=>array_merge($grid_column_array,array('emi_dueamount','total')),'totals'=>array('total','emi_dueamount','other_charges','emi_amount','due_panelty') ,'output_filename'=>$_GET['report_type'].' lilst_as_on '. $till_date.".csv"));

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

			$send = array('dealer'=>$form['dealer'],'till_date'=>$form['till_date']?:0,'report_type'=>$form['report_type'], 'loan_type'=>$form['loan_type'],'filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}		


	}
}