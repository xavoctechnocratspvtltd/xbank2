<?php

class page_member_dashboard extends Page{
	function init(){
		parent::init();
		$this->title = $this->api->auth->model['name'];

		$account_model=$this->add('Model_Account');
		$account_model->addCondition('member_id',$this->api->auth->model->id);

		$grid = $this->add('Grid_AccountStatement');
		$grid->setModel($account_model,array('AccountNumber','member','scheme','branch','account_type','ActiveStatus','ModeOfOperation','OpeningBalanceDr'));
		$grid->addPaginator(20);

		$grid->addMethod('format_AccountNumber',function($g,$f){
			$g->current_row_html[$f]='<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Account Statement',$g->api->url('member_statement',array('member_id'=>$g->model['member_id'],'account_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
		});

		$grid->addColumn('AccountNumber','AccountNumber');

		$grid->addFormatter('member','wrap');
		
		$grid->addMethod('format_Balance',function($g,$f){
			$openinig_blalnce = $g->model->getOpeningBalance($this->api->nextDate($this->api->today));
			$cr = $openinig_blalnce['CR'];
			$dr = $openinig_blalnce['DR'];

			$amount = $cr-$dr;
			$balance = $amount.' CR';
			if($amount < 0)
				$balance = abs($amount).' DR';

			$g->current_row_html[$f] = $balance;
		});
		
		$grid->addColumn('Balance','Balance');

		$order=$grid->addOrder();
		$order->move('AccountNumber','before','member')->now();
	}	
}