<?php
class page_reports_bs_balancesheet extends Page{
	public $title="Balance Sheet";
	function init(){
		parent::init();
		
		// $fy=$this->app->getFinancialYear();
		
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$branch_id = $this->app->current_branch->id == 1 ? null:$this->app->current_branch->id;

		$f=$this->add('Form',null,null,['form/stacked']);
		$c=$f->add('Columns')->addClass('row xepan-push');
		$l=$c->addColumn(6)->addClass('col-md-6');
		$r=$c->addColumn(6)->addClass('col-md-6');
		$l->addField('DatePicker','from_date')->set($from_date);
		$r->addField('DatePicker','to_date')->set($to_date);
		$f->addSubmit('Filter');

		$view = $this->add('View',null,null,['page/balancesheet']);
		// $view->add('View',null,'balancesheet_caption')->setHtml('<div> <h2 class="text-center"> BalanceSheet As On</h3> </div><div class="atk-row"><small class="atk-col-6 atk-text-dimmed"> From Date: '.$_GET['from_date']."</small><small class='atk-col-6 text-right atk-text-dimmed'> &nbsp;To Date: ".$_GET['to_date']. '</small></div>' );
		$view->add('View',null,'balancesheet_caption')->setHtml('<div> <h2 class="text-center"> BalanceSheet As On '. $_GET['to_date'].'</h3> </div>');

		if($f->isSubmitted()){
			$view->js()->reload(['from_date'=>$f['from_date']?:0,'to_date'=>$f['to_date']?:0])->execute();
		}

		if(!$from_date){
			$this->add('View')->set('Please select date range')->addClass('date-range-view');
			return;
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

		$op_balances_q='select s.balance_sheet_id id, sum(OpeningBalanceDr) DR, sum(OpeningBalanceCr) CR
						from accounts a join schemes s on a.scheme_id= s.id';
		if($branch_id) $op_balances_q .= ' WHERE a.branch_id = ' . $branch_id;
		$op_balances_q .= ' group by s.balance_sheet_id';

		$op_balances = $this->api->db->dsql()->expr($op_balances_q)->get();

		// var_dump($op_balances);

		$prev_transactions_q  = 'select balance_sheet_id id, sum(amountDr) DR, sum(amountCr) CR
								from transaction_row tr 
								join accounts a on tr.account_id = a.id
								where tr.created_at < "'.$from_date.'"';
		if($branch_id) $prev_transactions_q .= ' and a.branch_id = ' . $branch_id;
		$prev_transactions_q .=	' group by balance_sheet_id';
		$prev_transactions = $this->api->db->dsql()->expr($prev_transactions_q)->get();

		// var_dump($prev_transactions);

		$curr_trans_q='select balance_sheet_id id, sum(amountDr) DR, sum(amountCr) CR
					from transaction_row tr 
					join accounts a on tr.account_id = a.id
					where tr.created_at >= "'.$from_date.'" and tr.created_at < "'.$this->app->nextDate($to_date).'"';
		if($branch_id) $curr_trans_q .= ' and a.branch_id = ' . $branch_id;
		$curr_trans_q.=' group by balance_sheet_id';
		
		$curr_trans = $this->api->db->dsql()->expr($curr_trans_q)->get();

		// var_dump($curr_trans);

		$bs_array=[];
		$bs_model_rows = $this->add('Model_BalanceSheet')->getRows();
		foreach ($bs_model_rows as $bs_m) {
			$bs_m_id = $bs_m['id'];
			
			$data=[];
			$data['OpeningBalanceDr'] = 0;
			$data['OpeningBalanceCr'] = 0;
			$data['PreviousTransactionsDr'] = 0;
			$data['PreviousTransactionsCr'] = 0;
			$data['TransactionsDr'] = 0;
			$data['TransactionsCr'] = 0;

			$data['id']=$bs_m_id;
			$data['name'] = $bs_m['name'];
			foreach ($op_balances as $opb) {
				if($opb['id']==$bs_m_id){
					$data['OpeningBalanceDr'] = $opb['DR'];
					$data['OpeningBalanceCr'] = $opb['CR'];
				}
			}

			foreach ($prev_transactions as $opb) {
				if($opb['id']==$bs_m_id){
					$data['PreviousTransactionsDr'] = $opb['DR'];
					$data['PreviousTransactionsCr'] = $opb['CR'];
				}
			}

			foreach ($curr_trans as $opb) {
				if($opb['id']==$bs_m_id){
					$data['TransactionsDr'] = $opb['DR'];
					$data['TransactionsCr'] = $opb['CR'];
				}
			}

			$data['is_pandl'] = $bs_m['is_pandl'];
			$data['subtract_from'] = $bs_m['subtract_from'];
			$data['positive_side'] = $bs_m['positive_side'];

			if(!$bs_m['is_pandl']){
				$data['ClosingBalanceDr'] = $data['OpeningBalanceDr']+$data['PreviousTransactionsDr']+$data['TransactionsDr'];
				$data['ClosingBalanceCr'] = $data['OpeningBalanceCr']+$data['PreviousTransactionsCr']+$data['TransactionsCr'];
			}else{
				$data['ClosingBalanceDr'] = $data['TransactionsDr'];
				$data['ClosingBalanceCr'] = $data['TransactionsCr'];
			}

			if($data['ClosingBalanceCr']===0 && $data['ClosingBalanceCr']===0) continue;

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

			if(strtolower($bs['subtract_from'])=='cr'){
				$amount  = $cr_sum - $dr_sum;
			}else{
				$amount  = $dr_sum - $cr_sum;
			}
			// echo $amount. ' -- '. $bs['positive_side'] . ' -- ' . (($amount >=0 && strtolower($bs['positive_side'])=='lt')?'true':'false') . '<br/>';
			if($amount >=0){
				if(strtolower($bs['positive_side'])=='lt'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
				}
			}else{
				if(strtolower($bs['positive_side'])=='rt'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
				}
			}
		}

		// Add P&L
		$profit = 0;
		$loss = 0;

		foreach ($bs_array as $pl) {
			if(!$pl['is_pandl']) continue;
			$dr_sum = $pl['TransactionsDr'];
			$cr_sum = $pl['TransactionsCr'];

			if(strtolower($bs['subtract_from'])=='cr'){
				$amount  = $cr_sum - $dr_sum;
			}else{
				$amount  = $dr_sum - $cr_sum;
			}

			if($amount >=0){
				if(strtolower($pl['positive_side'])=='lt'){
					$loss += abs($amount);
				}else{
					$profit += abs($amount);
				}
			}else{
				if(strtolower($pl['positive_side'])=='rt'){
					$profit += abs($amount);
				}else{
					$loss += abs($amount);
				}
			}
		}

		if($profit > $loss){
			$profit -= $loss;
			$loss=0;
			$left_sum += abs($profit);
		}else{
			$loss -= $profit;
			$profit=0;
			$right_sum += abs($loss);
		}

		if($profit > 0){
			$left[] = ['name'=>'Profit','amount'=>abs($profit),'id'=>'profit'];	
		}

		if($loss > 0){
			$right[] = ['name'=>'Loss','amount'=>abs($loss),'id'=>'loss'];
		}

		// var_dump($left);

		$grid_l = $view->add('Grid_Template',null,'balancesheet_liablity',['view\grid\balancesheet-liablity']);
		$grid_l->setSource($left);
		$grid_l->template->trySet('lheading','Liablities');
		
		$grid_a = $view->add('Grid_Template',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->template->trySet('rheading','Assets');
		$grid_a->setSource($right);

		$view->template->trySet('ltotal',$left_sum);
		$view->template->trySet('atotal',$right_sum);
		$grid_l->addColumn('name');
		$grid_l->addFormatter('name','ExpanderPlain',['page'=>$this->api->url('reports_bs_bstoschemegroup',['branch_id'=>$branch_id])]);

		$grid_a->addColumn('name');
		$grid_a->addFormatter('name','ExpanderPlain',['page'=>$this->api->url('reports_bs_bstoschemegroup',['branch_id'=>$branch_id])]);

		// if($branch_id)
  //       	$view->js('click')->_selector('.xepan-accounts-bs-group')->univ()->frameURL('BalanceSheet Head Groups',[$this->api->url('reports_bs_bstoschemegroup'),'bs_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date, 'branch_id'=>$branch_id]);
  //       else
  //       	$view->js('click')->_selector('.xepan-accounts-bs-group')->univ()->frameURL('BalanceSheet Head Groups',[$this->api->url('reports_bs_bstoschemegroup'),'bs_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}