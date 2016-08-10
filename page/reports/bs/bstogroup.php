<?php

class page_reports_bs_bstogroup extends Page{
	public $title = "Balance Sheet Groups";
	function init(){
		parent::init();
				
		$bs_id = $this->api->stickyGET('bs_id');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$branch_id = $this->api->stickyGET('branch_id');


		// $st=$this->add('Model_Scheme');
		// $st->addCondition('balance_sheet_id',$bs_id);

		// foreach ($st as $s) {
		// 	echo $s['name']. '</br>';
		// }

		// return;

		// var_dump($this->api->db->dsql()->expr('select * from schemes where balance_sheet_id=2')->get());
		// exit;
		$op_balances_q='select a.scheme_id id, sum(IFNULL(OpeningBalanceDr,0)) DR, sum(IFNULL(OpeningBalanceCr,0)) CR
						from accounts a
						join schemes s on a.scheme_id = s.id
						WHERE s.balance_sheet_id = '.$bs_id.'
						';
		if($branch_id) $op_balances_q .= ' AND a.branch_id = ' . $branch_id;
		$op_balances_q .= ' group by a.scheme_id';

		$op_balances = $this->api->db->dsql()->expr($op_balances_q)->get();

		// var_dump($op_balances);

		$prev_transactions_q  = 'select a.scheme_id id, sum(IFNULL(amountDr,0)) DR, sum(IFNULL(amountCr,0)) CR
								from transaction_row tr 
								join accounts a on tr.account_id = a.id
								join schemes s on a.scheme_id = s.id
								where tr.created_at < "'.$from_date.'"
								and s.balance_sheet_id = '.$bs_id.'
								';
		if($branch_id) $prev_transactions_q .= ' and a.branch_id = ' . $branch_id;
		$prev_transactions_q .=	' group by a.scheme_id';
		$prev_transactions = $this->api->db->dsql()->expr($prev_transactions_q)->get();

		// var_dump($prev_transactions);

		$curr_trans_q='select a.scheme_id id, sum(IFNULL(amountDr,0)) DR, sum(IFNULL(amountCr,0)) CR
					from transaction_row tr 
					join accounts a on tr.account_id = a.id
					join schemes s on a.scheme_id = s.id
					where tr.created_at >= "'.$from_date.'" and tr.created_at < "'.$this->app->nextDate($to_date).'"
					and s.balance_sheet_id = '.$bs_id.'
					';
		if($branch_id) $curr_trans_q .= ' and a.branch_id = ' . $branch_id;
		$curr_trans_q.=' group by a.scheme_id';
		
		$curr_trans = $this->api->db->dsql()->expr($curr_trans_q)->get();

		$st=$this->add('Model_Scheme');
		$st->addCondition('balance_sheet_id',$bs_id);
		$st->join('balance_sheet','balance_sheet_id')->addField('is_pandl');

		$bs_array=[];

		foreach ($st as $scheme_m) {

			$data=[];
			$data['OpeningBalanceDr'] = 0;
			$data['OpeningBalanceCr'] = 0;
			$data['PreviousTransactionsDr'] = 0;
			$data['PreviousTransactionsCr'] = 0;
			$data['TransactionsDr'] = 0;
			$data['TransactionsCr'] = 0;

			$data['id']=$scheme_m->id;
			$data['name'] = $scheme_m['name'];
			foreach ($op_balances as $opb) {
				if($opb['id']==$scheme_m->id){
					$data['OpeningBalanceDr'] = $opb['DR'];
					$data['OpeningBalanceCr'] = $opb['CR'];
				}
			}

			foreach ($prev_transactions as $opb) {
				if($opb['id']==$scheme_m->id){
					$data['PreviousTransactionsDr'] = $opb['DR'];
					$data['PreviousTransactionsCr'] = $opb['CR'];
				}
			}

			foreach ($curr_trans as $opb) {
				if($opb['id']==$scheme_m->id){
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

			if($data['ClosingBalanceCr']==0 && $data['ClosingBalanceDr']==0) continue;

			$bs_array [] = $data;

		}


		// $bs_group = $this->add('Model_BS_Scheme',['from_date'=>$from_date,'to_date'=>$to_date]);
		// $bs_group->addCondition('balance_sheet_id',$bs_id);

		$grid = $this->add('Grid_Template',null,null,['view\grid\bstogroup']);
		$grid->setSource($bs_array);

		$bs = $this->add('Model_BalanceSheet')->load($bs_id);
		$grid->template->trySet('head',$bs['name']);
		$grid->template->trySet('from_date',$from_date);
		$grid->template->trySet('to_date',$to_date);

		// $grid->addTotals(['ClosingBalanceDr','ClosingBalanceCr']);
        $this->js('click')->_selector('.xepan-accounts-bs-subgroup')->univ()->frameURL('Groups And Ledger',[$this->api->url('groupdig'),'group_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}