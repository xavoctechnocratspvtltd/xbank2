<?php

class page_reports_bs_bstoschemegroup extends Page{
	public $title = "Balance Sheet Groups";
	function init(){
		parent::init();
				
		$bs_id = $this->api->stickyGET('_id');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$branch_id = $this->api->stickyGET('branch_id');		

		$op_balances_q='select s.SchemeGroup id, sum(IFNULL(OpeningBalanceDr,0)) DR, sum(IFNULL(OpeningBalanceCr,0)) CR
						from accounts a
						join schemes s on a.scheme_id = s.id
						WHERE s.balance_sheet_id = '.$bs_id.'
						';
		if($branch_id) $op_balances_q .= ' AND a.branch_id = ' . $branch_id;
		$op_balances_q .= ' group by s.SchemeGroup';

		$op_balances = $this->api->db->dsql()->expr($op_balances_q)->get();

		// var_dump($op_balances);

		$prev_transactions_q  = 'select s.SchemeGroup id, sum(IFNULL(amountDr,0)) DR, sum(IFNULL(amountCr,0)) CR
								from transaction_row tr 
								join accounts a on tr.account_id = a.id
								join schemes s on a.scheme_id = s.id
								where tr.created_at < "'.$from_date.'"
								and s.balance_sheet_id = '.$bs_id.'
								';
		if($branch_id) $prev_transactions_q .= ' and a.branch_id = ' . $branch_id;
		$prev_transactions_q .=	' group by s.SchemeGroup';
		$prev_transactions = $this->api->db->dsql()->expr($prev_transactions_q)->get();

		// var_dump($prev_transactions);

		$curr_trans_q='select s.SchemeGroup id, sum(IFNULL(amountDr,0)) DR, sum(IFNULL(amountCr,0)) CR
					from transaction_row tr 
					join accounts a on tr.account_id = a.id
					join schemes s on a.scheme_id = s.id
					where tr.created_at >= "'.$from_date.'" and tr.created_at < "'.$this->app->nextDate($to_date).'"
					and s.balance_sheet_id = '.$bs_id.'
					';
		if($branch_id) $curr_trans_q .= ' and a.branch_id = ' . $branch_id;
		$curr_trans_q.=' group by s.SchemeGroup';
		
		$curr_trans = $this->api->db->dsql()->expr($curr_trans_q)->get();

		$st=$this->add('Model_Scheme');
		$st->addCondition('balance_sheet_id',$bs_id);
		$st->join('balance_sheet','balance_sheet_id')->addField('is_pandl');
		$st->_dsql()->group('SchemeGroup');

		$total_dr = 0; 
		$total_cr = 0; 
		$bs_array=[];

		foreach ($st as $scheme_m) {

			$data=[];
			$data['OpeningBalanceDr'] = 0;
			$data['OpeningBalanceCr'] = 0;
			$data['PreviousTransactionsDr'] = 0;
			$data['PreviousTransactionsCr'] = 0;
			$data['TransactionsDr'] = 0;
			$data['TransactionsCr'] = 0;

			$data['id']=$scheme_m['SchemeGroup'];
			$data['name'] = $scheme_m['SchemeGroup'];
			foreach ($op_balances as $opb) {
				if($opb['id']==$scheme_m['SchemeGroup']){
					$data['OpeningBalanceDr'] = $opb['DR'];
					$data['OpeningBalanceCr'] = $opb['CR'];
				}
			}

			foreach ($prev_transactions as $opb) {
				if($opb['id']==$scheme_m['SchemeGroup']){
					$data['PreviousTransactionsDr'] = $opb['DR'];
					$data['PreviousTransactionsCr'] = $opb['CR'];
				}
			}

			foreach ($curr_trans as $opb) {
				if($opb['id']==$scheme_m['SchemeGroup']){
					$data['TransactionsDr'] = $opb['DR'];
					$data['TransactionsCr'] = $opb['CR'];
				}
			}

			// echo $scheme_m['name']. ' -- '.  $scheme_m['is_pandl'] . '<br/>';

			if(!$scheme_m['is_pandl']){
				$data['ClosingBalanceDr'] = $data['OpeningBalanceDr']+$data['PreviousTransactionsDr']+$data['TransactionsDr'];
				$data['ClosingBalanceCr'] = $data['OpeningBalanceCr']+$data['PreviousTransactionsCr']+$data['TransactionsCr'];
			}else{
				$data['ClosingBalanceDr'] = $data['TransactionsDr'];
				$data['ClosingBalanceCr'] = $data['TransactionsCr'];
			}

			if(($data['ClosingBalanceCr']==0 && $data['ClosingBalanceDr']==0) || ($data['ClosingBalanceCr']==$data['ClosingBalanceDr'] )) continue;

			if($data['ClosingBalanceCr'] > $data['ClosingBalanceDr']){
				$data['ClosingBalanceCr'] -= $data['ClosingBalanceDr'];
				$data['ClosingBalanceDr']=0;
			}else{
				$data['ClosingBalanceDr'] -= $data['ClosingBalanceCr'];
				$data['ClosingBalanceCr']=0;
			}

			$total_dr += $data['ClosingBalanceDr'];
			$total_cr += $data['ClosingBalanceCr'];
			$bs_array [] = $data;
		}

		// $bs_group = $this->add('Model_BS_Scheme',['from_date'=>$from_date,'to_date'=>$to_date]);
		// $bs_group->addCondition('balance_sheet_id',$bs_id);

		$grid = $this->add('Grid_Template',null,'grid',['view\grid\bstogroup']);
		$grid->setSource($bs_array);

		// $bs = $this->add('Model_BalanceSheet')->load($bs_id);
		// $grid->template->trySet('head',$bs['name']);
		// $grid->template->trySet('from_date',$from_date);
		// $grid->template->trySet('to_date',$to_date);
		
		// $this->template->trySet('dr_total',$total_dr);
		// $this->template->trySet('cr_total',$total_cr);

		// $grid->addTotals(['ClosingBalanceDr','ClosingBalanceCr']);
        if($branch_id)
        	$this->js('click',$this->js()->_selector('.xepan-accounts-bs-subgroup')->addClass('addedevent'))->_selector('.xepan-accounts-bs-subgroup:not(.addedevent)')->univ()->frameURL('Groups And Ledger',[$this->api->url('reports_bs_schemegrouptoaccounts'),'scheme_group'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date, 'branch_id'=>$branch_id]);
		else
        	$this->js('click',$this->js()->_selector('.xepan-accounts-bs-subgroup')->addClass('addedevent'))->_selector('.xepan-accounts-bs-subgroup:not(.addedevent)')->univ()->frameURL('Groups And Ledger',[$this->api->url('reports_bs_schemegrouptoaccounts'),'scheme_group'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}

	function defaultTemplate(){
		return['page\bstoscheme'];
	}
}