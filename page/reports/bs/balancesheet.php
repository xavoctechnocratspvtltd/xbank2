<?php
class page_reports_bs_balancesheet extends Page{
	public $title="Balance Sheet";
	function init(){
		parent::init();
		
		$fy=$this->app->getFinancialYear();
		
		$from_date = $this->api->stickyGET('from_date')?:$fy['start_date'];
		$to_date = $this->api->stickyGET('to_date')?:$fy['end_date'];
		$branch_id = $this->app->current_branch->id;

		$f=$this->add('Form',null,null,['form/stacked']);
		$c=$f->add('Columns')->addClass('row xepan-push');
		$l=$c->addColumn(6)->addClass('col-md-6');
		$r=$c->addColumn(6)->addClass('col-md-6');
		$l->addField('DatePicker','from_date')->set($from_date);
		$r->addField('DatePicker','to_date')->set($to_date);
		$f->addSubmit('Filter');

		$view = $this->add('View',null,null,['page/balancesheet']);

		if($f->isSubmitted()){
			return $view->js()->reload(['from_date'=>$f['from_date']?:0,'to_date'=>$f['to_date']?:0])->execute();
		}


		/*
	
		select balance_sheet_id, sum(amountDr), sum(amountCr)
		from transaction_row tr 
		where tr.created_at < '2015-04-01'
		group by balance_sheet_id

		select balance_sheet_id, sum(amountDr), sum(amountCr)
		from transaction_row tr 
		where tr.created_at >= '2015-04-01' and tr.created_at < '2016-04-01'
		group by balance_sheet_id

		select s.balance_sheet_id, sum(OpeningBalanceDr), sum(OpeningBalanceCr)
		from accounts a join schemes s on a.scheme_id= s.id
		group by s.balance_sheet_id

		*/

		$op_balances_q='select s.balance_sheet_id, sum(OpeningBalanceDr), sum(OpeningBalanceCr)
						from accounts a join schemes s on a.scheme_id= s.id';
		if($branch_id) $op_balances_q .= ' WHERE a.branch_id = ' . $branch_id;
		$op_balances_q .= ' group by s.balance_sheet_id';

		$op_balances = $this->api->db->dsql()->expr($op_balances_q)->get();

		// var_dump($op_balances);

		$prev_transactions_q  = 'select balance_sheet_id, sum(amountDr), sum(amountCr)
								from transaction_row tr 
								join accounts a on tr.account_id = a.id
								where tr.created_at < "'.$from_date.'"';
		if($branch_id) $prev_transactions_q .= ' and a.branch_id = ' . $branch_id;
		$prev_transactions_q .=	' group by balance_sheet_id';
		$prev_transactions = $this->api->db->dsql()->expr($prev_transactions_q)->get();

		// var_dump($prev_transactions);

		$curr_trans_q='select balance_sheet_id, sum(amountDr), sum(amountCr)
					from transaction_row tr 
					join accounts a on tr.account_id = a.id
					where tr.created_at >= "'.$from_date.'" and tr.created_at < "'.$this->app->nextDate($to_date).'"';
		if($branch_id) $curr_trans_q .= ' and a.branch_id = ' . $branch_id;
		$curr_trans_q.=' group by balance_sheet_id';
		
		$curr_trans = $this->api->db->dsql()->expr($curr_trans_q)->get();

		// var_dump($curr_trans);

		$bs_array=[];

		foreach ($this->add('Model_BalanceSheet') as $bs_m) {
			$data=[];
			$data['id']=$bs_m->id;
			$data['name'] = $bs_m['name'];
			foreach ($op_balances as $opb) {
				if($opb['balance_sheet_id']==$bs_m->id){
					$data['OpeningBalanceDr'] = $opb['sum(OpeningBalanceDr)'];
					$data['OpeningBalanceCr'] = $opb['sum(OpeningBalanceCr)'];
				}
			}

			foreach ($prev_transactions as $opb) {
				if($opb['balance_sheet_id']==$bs_m->id){
					$data['PreviousTransactionsDr'] = $opb['sum(amountDr)'];
					$data['PreviousTransactionsCr'] = $opb['sum(amountCr)'];
				}
			}

			foreach ($curr_trans as $opb) {
				if($opb['balance_sheet_id']==$bs_m->id){
					$data['TransactionsDr'] = $opb['sum(amountDr)'];
					$data['TransactionsCr'] = $opb['sum(amountCr)'];
				}
			}

			$data['is_pandl'] = $bs_m['is_pandl'];

			$bs_array [] = $data;

		}

		// var_dump($bs_array);

		$left=[];
		$right=[];

		$left_sum=0;
		$right_sum=0;

		foreach ($bs_array as $bs) {
			if($bs['is_pandl']) continue;

			$dr_sum = $bs['OpeningBalanceDr']+$bs['PreviousTransactionsDr']+$bs['TransactionsDr'];
			$cr_sum = $bs['OpeningBalanceCr']+$bs['PreviousTransactionsCr']+$bs['TransactionsCr'];

			if($bs['subtract_from']=='CR'){
				$amount  = $cr_sum - $dr_sum;
			}else{
				$amount  = $dr_sum - $cr_sum;
			}
			if($amount >=0 && $bs['positive_side']=='LT'){
				$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
				$left_sum += abs($amount);
			}else{
				$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
				$right_sum += abs($amount);
			}
		}

		// Add P&L
		$profit = 0;
		$loss = 0;

		foreach ($bs_array as $pl) {
			if(!$pl['is_pandl']) continue;
			$dr_sum = $pl['TransactionsDr'];
			$cr_sum = $pl['TransactionsCr'];

			if($bs['subtract_from']=='CR'){
				$amount  = $cr_sum - $dr_sum;
			}else{
				$amount  = $dr_sum - $cr_sum;
			}

			if($amount >=0 && $pl['positive_side']=='LT'){
				$left_sum += abs($amount);
				$profit += abs($amount);
			}else{
				$right_sum += abs($amount);
				$loss += abs($amount);
			}
		}

		if($profit >= 0){
			$left[] = ['name'=>'Profit','amount'=>abs($profit)];	
		}

		if($loss > 0){
			$right[] = ['name'=>'Loss','amount'=>abs($loss)];
		}


		$grid_l = $view->add('Grid_Template',null,'balancesheet_liablity',['view\grid\balancesheet-liablity']);
		$grid_l->setSource($left);
		$grid_l->template->trySet('lheading','Liablities');
		
		$grid_a = $view->add('Grid_Template',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->template->trySet('rheading','Assets');
		$grid_a->setSource($right);

		$view->template->trySet('ltotal',$left_sum);
		$view->template->trySet('atotal',$right_sum);

  //       $view->js('click')->_selector('.xepan-accounts-bs-group')->univ()->frameURL('BalanceSheet Head Groups',[$this->api->url('xepan_accounts_bstogroup'),'bs_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}