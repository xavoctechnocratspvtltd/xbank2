<?php

class page_dsa_emiduelist extends Page {

	public $title="EMI Due List (Recovery List)";

	function init(){
		parent::init();

		$form=$this->add('Form');
		$grid=$this->add('Grid_AccountsBase'); 
		$till_date = $this->api->today;

		if($_GET['till_date']){
			$till_date=$this->api->stickyGET('till_date');
		}
		$grid->add('H3',null,'grid_buttons')->set('Loan EMI Due List As On '. date('d-M-Y',strtotime($till_date)));

		$active_dealer_model=$this->add('Model_ActiveDealer');
		$active_dealer_model->addCondition('dsa_id',$this->api->auth->model->id);
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel($active_dealer_model);

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
		$member_join=$account_model->join('members','member_id');
		$member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');

		$account_model->addCondition('DefaultAC',false);

		$account_model_j=$account_model->join('premiums.account_id','id');
		$account_model_j->addField('DueDate');
		$account_model->addCondition('MaturedStatus',false); //???
		$dealer_j=$account_model->join('dealers','dealer_id');
		$dsa_j=$dealer_j->join('dsa','dsa_id');
		// $dsa_j->addField('dsa','name');
		$grid_column_array = array('AccountNumber','created_at','maturity_date','DueDate','scheme','member_name','FatherName','PermanentAddress','PhoneNos','dealer','guarantor_name','guarantor_phno','last_premium','paid_premium_count','due_premium_count','emi_amount','due_panelty','other_charges','total');
		$account_model->addExpression('paid_premium_count')->set(function($m,$q)use($till_date){
			return $m->refSQL('Premium')
						->addCondition('PaidOn','<>',null)
						->addCondition('DueDate','<',$m->api->nextDate($till_date))
						->count();
		})->sortable(true);

		$account_model->addExpression('due_premium_count')->set(function($m,$q)use($till_date){
			return $m->refSQL('Premium')
						->addCondition('PaidOn',null)
						->addCondition('DueDate','<',$m->api->nextDate($till_date))
						->count();
		});

		$account_model->addExpression('last_premium')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('DueDate');
		});

		$account_model->addExpression('emi_amount')->set(function($m,$q){
			return $m->RefSQL('Premium')->setOrder('id','desc')->setLimit(1)->fieldQuery('Amount');
		});

		$account_model->addExpression('due_panelty')->set(function($m,$q)use($till_date){
			return $m->refSQL('Premium')->addCondition('PaneltyCharged','<>',$m->api->db->dsql()->expr('PaneltyPosted'))->addCondition('DueDate','<',$till_date)->sum($m->dsql()->expr('PaneltyCharged - PaneltyPosted'));
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


		$account_model->addExpression('guarantor_phno')->set(function($m,$q){
			$guarantor_m = $m->add('Model_Member',array('table_alias'=>'guarantor_name_q'));
			$ac_join = $guarantor_m->join('account_guarantors.member_id');
			$ac_join->addField('account_id');
			$guarantor_m->addCondition('account_id',$q->getField('id'));
			$guarantor_m->setLimit(1);
			$guarantor_m->setOrder('id');
			return $guarantor_m->_dsql()->del('fields')->field($guarantor_m ->table_alias.'.PhoneNos');
		});
		

		
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('dealer');
			$this->api->stickyGET('report_type');
			$this->api->stickyGET('till_date');
			$this->api->stickyGET('loan_type');
			$this->api->stickyGET('report_type');

			$account_model->addCondition('DueDate','<=',$till_date);

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
					$account_model->addCondition('last_premium','<',$till_date);
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

			$grid->addMethod('format_total',function($g,$f){

				$g->current_row_html[$f]= ($g->model['due_premium_count'] * $g->model['emi_amount']) +$g->model['due_panelty']+$g->model['other_charges'];

			});

			$grid->addMethod('format_emidue',function($g,$f){

				$g->current_row_html[$f]=$g->model['due_premium_count']*$g->model['emi_amount'];

			});

			$grid->addColumn('total','total');
			$grid->addColumn('emidue','emi_dueamount');


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

		$account_model->_dsql()->group('id');
		// $account_model->add('Controller_Acl');
		$grid->setModel($account_model,$grid_column_array);

		// $grid->addColumn('text','openning_date');

		$grid->addPaginator(100);
		$grid->addSno();
		$grid->addTotals(array('total','emi_dueamount','other_charges','emi_amount'));
		$grid->removeColumn('last_premium');
		$js=array(
			// $this->js()->_selector('.atk-layout-row')->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			);

		$grid->js('click',$js);

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