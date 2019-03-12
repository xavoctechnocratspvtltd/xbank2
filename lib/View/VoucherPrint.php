<?php

class View_VoucherPrint extends View{
	public $transaction_id;
	function recursiveRender(){
		$css=array(
			'templates/css/epan.css',
			'templates/css/compact.css'
		);
		
		foreach ($css as $css_file) {
			$link = $this->add('View')->setElement('link');
			$link->setAttr('rel',"stylesheet");
			$link->setAttr('type',"text/css");
			$link->setAttr('href',$css_file);
		}	

		$this->api->stickyGET('transaction_id');
		if($_GET['transaction_id'])
			$this->transaction_id = $_GET['transaction_id'];


		$transaction = $this->add('Model_Transaction');
		$transaction->getElement('staff_id')->getModel()->title_field = "username";
		$transaction->load($this->transaction_id);
		
		$v = $this->add('View')->addClass('atk-padding-small');
		$v->add('H2')->set('BHAWANI CREDIT CO OPERATIVE SOCIETY')->setStyle('margin-bottom','-10px');
		$v->add('H4')->set('Voucher No: '. $transaction['voucher_no'].' UUID : '.$transaction->id);
		
		$cols= $v->add('Columns');
		$left=$cols->addColumn(6);
		$right=$cols->addColumn(6);

		$left->add('View')->set(array('Transaction Date : ' . $transaction['created_at'],'icon'=>'calendar'));
		$right->add('View')->set(array($transaction['transaction_type'],'icon'=>'check'));
		$grid=$v->add('Grid');
		$grid->setModel($transaction->ref('TransactionRow')->setOrder('amountDr desc, amountCr desc'),array('account','amountDr','amountCr'));

		$v->add('View')->set(array($transaction['Narration'],'icon'=>'pencil'));
		$v->add('View')->set(array($transaction['reference']." created by ".$transaction['staff'],'icon'=>'pencil'));


		if(!$_GET['hide_print_btn']){
			$btn=$this->add('Button')->set('print')->addClass('pull-right');
			if($btn->isClicked()){
				$this->js()->univ()->newWindow($this->api->url('voucher_print',array('transaction_id'=>$this->transaction_id,'hide_print_btn'=>1,'cut_page'=>0)))->execute();
			}
		}
		$v->add('View')->addClass('atk-padding-small');
		$fcol = $v->add('Columns');
		$fcol_1 = $fcol->addColumn(3);
		$fcol_2 = $fcol->addColumn(3);
		$fcol_3 = $fcol->addColumn(3);
		$fcol_4 = $fcol->addColumn(3)->addClass('atk-align-center');

		$fcol_1->add('H4')->set('Cashier_________')->setStyle('margin-top','-10px');
		$fcol_1->add('H4')->set('B.m.____________')/*->setStyle('margin-top','-10px')*/;

		$fcol_2->add('H4')->set('Entry By________')->setStyle('margin-top','-10px');
		$fcol_2->add('H4')->set('Auditor_________');
		
		$fcol_3->add('H4')->set('T. No___________')->setStyle('margin-top','-10px');

		$fcol_4->add('H5')->set('Revenue Stamp')->addClass('atk-align-center')->setStyle(array('border'=>'1px solid black','width'=>"60px",'height'=>'70px','margin'=>'-30px auto 0 auto'));
		$fcol_4->add('H4')->set('Receiver\'s Stamp')->setStyle('margin-top','-3px');

		$v->add('HR')/*->addClass('atk-padding-small')*/;

		parent::recursiveRender();
	}

}