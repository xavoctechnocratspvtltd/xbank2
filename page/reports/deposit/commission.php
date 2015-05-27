<?php

class page_reports_deposit_commission extends Page {
	public $title="Commission Report";
	function init(){
		parent::init();

		// TRA_ACCOUNT_OPEN_AGENT_COMMISSION, TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT
		// TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT
 
		$form=$this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();

		$account_type_array=array('%'=>'All','DDS'=>'DDS','FixedAndMis'=>'Fixed And Mis','Recurring'=>'Recurring');


		$form->addField('dropdown','account_type')->setValueList($account_type_array);
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Commission Report From ' . date('01-m-Y',strtotime($_GET['from_date'])). ' to ' . date('t-m-Y',strtotime($_GET['to_date'])) );

		$transaction_row=$this->add('Model_TransactionRow');
		$transaction_row->getElement('amountCr')->caption('Net Commission')->type('int');
		$transaction_join = $transaction_row->join('transactions','transaction_id');
		// $transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');

		$account_join = $transaction_row->join('accounts','account_id');
		$agent_join = $account_join->join('agents.account_id');
		$agent_join->addField('agent_id_for_account','id');
		$agent_sb_join = $agent_join->join('accounts','account_id');
		$agent_sb_join->addField('agent_account_number','AccountNumber');
		$member_join=$agent_join->join('members','member_id');
		$member_join->addField('agent_name','name');


		$referance_account_join = $transaction_join->join('accounts','reference_id');
		$referance_account_join->addField('AccountNumber');
		$referance_account_join->addField('Amount');
		$reference_member_join = $referance_account_join->join('members','member_id');
		$reference_member_join->addField('member_name','name');
		$referance_account_scheme_join = $referance_account_join->join('schemes','scheme_id');
		$referance_account_scheme_join->addField('ref_account_scheme_type','SchemeType');
		$referance_account_scheme_join->addField('ref_account_scheme_name','name');



		if($_GET['filter']){
			$this->api->stickyGET('filter');

			if($_GET['agent']){
				$this->api->stickyGET("agent");
				$transaction_row->addCondition('agent_id_for_account',$_GET['agent']);
			}

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$transaction_row->addCondition('created_at','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$transaction_row->addCondition('created_at','<',$_GET['to_date']);
			}  

			if($_GET['account_type']){
				$this->api->stickyGET("account_type");
				$transaction_row->addCondition('ref_account_scheme_type','like',$_GET['account_type']);
			}

		}else
			$transaction_row->addCondition('id',-1);

		$grid->addSno();

		$transaction_row->_dsql()->having(
            $transaction_row->dsql()->orExpr()
                ->where('transaction_type', TRA_ACCOUNT_OPEN_AGENT_COMMISSION)
                ->where('transaction_type', TRA_PREMIUM_AGENT_COMMISSION_DEPOSIT)
        );

		$transaction_row->setOrder('created_at','desc');
		$transaction_row->add('Controller_Acl');

		$grid->setModel($transaction_row,array('AccountNumber','member_name','Amount','ref_account_scheme_name','created_at','total_commission','voucher_no','transaction_type','amountCr','agent_name','agent_account_number'));
		$grid->addFormatter('voucher_no','voucherNo');

		$grid->addMethod('format_totalCommission',function($g,$f){
			$m=$g->add('Model_TransactionRow');
			$m->addCondition('transaction_id',$g->model->dsql()->expr($g->model['transaction_id']));
			$m->addCondition('amountDr','>',0);
			
			$a_j = $m->join('accounts','account_id');
			$a_j->addField('AccountNumber');

			$m->addCondition('AccountNumber','like','%Commission Paid On%');

			$m->setLimit(1);

			$m->_dsql()->del('fields')->field('amountDr');
			// $m->tryLoadAny();
			$g->current_row[$f]=$m->_dsql()->getOne();
		});

		$grid->addMethod('format_tds',function($g,$f){
			$g->current_row[$f]=$g->current_row['total_commission'] - $g->current_row['amountCr'];
		});
		
		$grid->addColumn('totalCommission','total_commission');
		$grid->addColumn('tds','tds','TDS');
		$grid->addPaginator(50);
		// $grid->addTotals(array('tds'));

		$grid->removeColumn('transaction_type');

		$grid->addOrder()->move('amountCr','after','total_commission')->now();
		
		// $grid->addColumn('expander','accounts');
		if($form->isSubmitted()){
			$grid->js()->reload(array('agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'account_type'=>$form['account_type'],'filter'=>1))->execute();
		}

	}
}