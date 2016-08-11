<?php

class page_reports_pandl_pandlgrouptoaccounts extends Page{
	function init(){
		parent::init();

		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$branch_id = $this->api->stickyGET('branch_id');
		$pandl_group = $this->api->stickyGET('pandl_group');

		$op_balances_q='select a.AccountNumber id, IFNULL(OpeningBalanceDr,0) DR, IFNULL(OpeningBalanceCr,0) CR, bs.is_pandl is_pandl
						from accounts a
						join schemes s on a.scheme_id = s.id
						join balance_sheet bs on s.balance_sheet_id=bs.id
						WHERE a.PAndLGroup = "'.$pandl_group.'"
						';
		if($branch_id) $op_balances_q .= ' AND a.branch_id = ' . $branch_id;

		$op_balances = $this->api->db->dsql()->expr($op_balances_q)->get();

		$prev_transactions_q  = 'select a.AccountNumber id, sum(IFNULL(amountDr,0)) DR, sum(IFNULL(amountCr,0)) CR
								from transaction_row tr 
								join accounts a on tr.account_id = a.id
								join schemes s on a.scheme_id = s.id
								where tr.created_at < "'.$from_date.'"
								and a.PAndLGroup = "'.$pandl_group.'"
								';
		if($branch_id) $prev_transactions_q .= ' and a.branch_id = ' . $branch_id;
		$prev_transactions_q.=' group by tr.account_id';
		$prev_transactions = $this->api->db->dsql()->expr($prev_transactions_q)->get();

		// var_dump($prev_transactions);

		$curr_trans_q='select a.AccountNumber id, sum(IFNULL(amountDr,0)) DR, sum(IFNULL(amountCr,0)) CR
					from transaction_row tr 
					join accounts a on tr.account_id = a.id
					join schemes s on a.scheme_id = s.id
					where tr.created_at >= "'.$from_date.'" and tr.created_at < "'.$this->app->nextDate($to_date).'"
					and a.PAndLGroup = "'.$pandl_group.'"
					';
		if($branch_id) $curr_trans_q .= ' and a.branch_id = ' . $branch_id;
		$curr_trans_q.=' group by tr.account_id';
		$curr_trans = $this->api->db->dsql()->expr($curr_trans_q)->get();

		$total_dr = 0; 
		$total_cr = 0; 
		$data_array=[];

		// $accounts=$this->add('Model_Account');
		// $s_j = $accounts->join('schemes','scheme_id');
		// $s_j->addField('SchemeGroup');
		// $s_j->join('balance_sheet','balance_sheet_id')->addField('is_pandl');
		// $accounts->addCondition('SchemeGroup',$scheme_group);


		foreach ($op_balances as $acc) {
			// echo $acc['AccountNumber']. '<br/>';
			// continue;
			$data=[];
			$data['OpeningBalanceDr'] = 0;
			$data['OpeningBalanceCr'] = 0;
			$data['PreviousTransactionsDr'] = 0;
			$data['PreviousTransactionsCr'] = 0;
			$data['TransactionsDr'] = 0;
			$data['TransactionsCr'] = 0;

			$data['id']=$acc['id'];
			$data['name'] = $acc['id'];
			$data['is_pandl'] = $acc['is_pandl'];
			foreach ($op_balances as $opb) {
				if($opb['id']==$acc['id']){
					$data['OpeningBalanceDr'] = $opb['DR'];
					$data['OpeningBalanceCr'] = $opb['CR'];
				}
			}

			// echo $scheme_m['name']. ' -- '.  $scheme_m['is_pandl'] . '<br/>';

			$data_array [$acc['id']] = $data;
		}

		foreach ($prev_transactions as $opb) {			
				$data_array [$opb['id']]['PreviousTransactionsDr'] = $opb['DR'];
				$data_array [$opb['id']]['PreviousTransactionsCr'] = $opb['CR'];
		}

		foreach ($curr_trans as $opb) {
			$data_array [$opb['id']]['TransactionsDr'] = $opb['DR'];
			$data_array [$opb['id']]['TransactionsCr'] = $opb['CR'];
		}

		foreach ($data_array as $id => &$data) {
			if(!$data['is_pandl']){
				$data['ClosingBalanceDr'] = $data['OpeningBalanceDr']+$data['PreviousTransactionsDr']+$data['TransactionsDr'];
				$data['ClosingBalanceCr'] = $data['OpeningBalanceCr']+$data['PreviousTransactionsCr']+$data['TransactionsCr'];
			}else{
				$data['ClosingBalanceDr'] = $data['TransactionsDr'];
				$data['ClosingBalanceCr'] = $data['TransactionsCr'];
			}

			if(($data['ClosingBalanceCr']==0 && $data['ClosingBalanceDr']==0) || ($data['ClosingBalanceCr']==$data['ClosingBalanceDr'] )){
				unset($data_array[$id]);
				continue;
			}

			if($data['ClosingBalanceCr'] > $data['ClosingBalanceDr']){
				$data['ClosingBalanceCr'] -= $data['ClosingBalanceDr'];
				$data['ClosingBalanceDr']=0;
			}else{
				$data['ClosingBalanceDr'] -= $data['ClosingBalanceCr'];
				$data['ClosingBalanceCr']=0;
			}

			$total_dr += $data['ClosingBalanceDr'];
			$total_cr += $data['ClosingBalanceCr'];
		}

		// echo "<pre>";
		// print_r($data_array);
		// echo "</pre>";
		// return;

		// var_dump($data_array);
		// return;

		$grid = $this->add('Grid_Template',null,'grid',['view\grid\bstogroup']);
		$grid->setSource($data_array);

		// $bs = $this->add('Model_Scheme')->loadBy('SchemeGroup',$scheme_group);
		$grid->template->trySet('head',$pandl_group);
		$grid->template->trySet('from_date',$from_date);
		$grid->template->trySet('to_date',$to_date);

		$this->template->trySet('dr_total',$total_dr);
		$this->template->trySet('cr_total',$total_cr);

		// $grid->addTotals(['ClosingBalanceDr','ClosingBalanceCr']);
        if($branch_id)
        	$this->js('click')->_selector('.xepan-accounts-bs-subgroup')->univ()->frameURL('Account',[$this->api->url('accounts_statement'),'AccountNumber'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date, 'branch_id',$branch_id]);
        else
        	$this->js('click')->_selector('.xepan-accounts-bs-subgroup')->univ()->frameURL('Account',[$this->api->url('accounts_statement'),'AccountNumber'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}

	function defaultTemplate(){
		return['page\bstoscheme'];
	}
}