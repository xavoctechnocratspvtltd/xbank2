<?php

class page_member_dashboard extends Page{
	function init(){
		parent::init();
		$this->title = $this->api->auth->model['name'];

		$account_model=$this->add('Model_Account');
		$account_model->addCondition('member_id',$this->api->auth->model->id);

		$grid = $this->add('Grid_AccountStatement');
		$grid->setModel($account_model);//,array('reference_id','voucher_no','created_at','Narration','amountDr','amountCr'));
		$grid->addPaginator(20);

		$grid->addMethod('format_AccountNumber',function($g,$f){
			$g->current_row_html[$f]='<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Account Statement',$g->api->url('member_statement',array('member_id'=>$g->model['member_id'],'account_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
		});

		$grid->addColumn('AccountNumber','AccountNumber');

		$order=$grid->addOrder();
		$order->move('AccountNumber','before','member')->now();
	}	
}