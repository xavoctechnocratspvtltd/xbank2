<?php
class page_reports_bs_balancesheet extends Page{
	public $title="Balance Sheet";
	function init(){
		parent::init();

		$fy=$this->app->getFinancialYear();
		
		$from_date = $this->api->stickyGET('from_date')?:$fy['start_date'];
		$to_date = $this->api->stickyGET('to_date')?:$fy['end_date'];

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

		$bsbalancesheet = $view->add('Model_BS_BalanceSheet',['from_date'=>$from_date,'to_date'=>$to_date]);
		$bsbalancesheet->addCondition('is_pandl',false);

		$left=[];
		$right=[];

		$left_sum=0;
		$right_sum=0;

		foreach ($bsbalancesheet as $bs) {
			if($bs['subtract_from']=='CR'){
				$amount  = $bs['ClosingBalanceCr'] - $bs['ClosingBalanceDr'];
			}else{
				$amount  = $bs['ClosingBalanceDr'] - $bs['ClosingBalanceCr'];
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
		$pandl = $view->add('xepan\accounts\Model_BS_BalanceSheet',['from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']]);
		$pandl->addCondition('pandl',true);

		foreach ($pandl as $pl) {
			if($pl['subtract_from']=='CR'){
				$amount  = $pl['ClosingBalanceCr'] - $pl['ClosingBalanceDr'];
			}else{
				$amount  = $pl['ClosingBalanceDr'] - $pl['ClosingBalanceCr'];
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


		$grid_l = $view->add('Grid',null,'balancesheet_liablity',['view\grid\balancesheet-liablity']);
		$grid_l->setSource($left);
		$grid_l->template->trySet('lheading','Liablities');
		
		$grid_a = $view->add('xepan\hr\Grid',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->template->trySet('rheading','Assets');
		$grid_a->setSource($right);

		// $view->template->trySet('ltotal',$left_sum);
		// $view->template->trySet('atotal',$right_sum);

  //       $view->js('click')->_selector('.xepan-accounts-bs-group')->univ()->frameURL('BalanceSheet Head Groups',[$this->api->url('xepan_accounts_bstogroup'),'bs_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}