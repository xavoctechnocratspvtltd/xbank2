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
		$url = $this->api->url('voucher_print');
		$transactions = $this->add('Model_Transaction');
		$transactions->addCondition('reference_id',$g->model->id);
		// $transaction_id =($this->model instanceof Model_TransactionRow)? $this->model['transaction_id']: $this->model->id;
		$g->current_row_html[$f] = "<a href='#voucher' class='voucher' onclick='$(this).univ().frameURL(\"Transaction Voucher ". $transactions['voucher_no'] ." uuid:". $g->model->id ."\",\"" .
                    $url->set(array(
                        $f => $g->model->id,
                        'transaction_id'=>$g->model->id,
                        $this->name.'_'.$f => $g->model->id
                    )) . "\")'".
            ">".$g->current_row[$f]."</a>";
		});

		$grid->addColumn('AccountNumber','AccountNumber');

		$order=$grid->addOrder();
		$order->move('AccountNumber','before','member')->now();
	}	
}