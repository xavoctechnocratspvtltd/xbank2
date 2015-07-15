<?php

class page_voucher_print extends Page {
	function init(){
		parent::init();
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

		$transaction = $this->add('Model_Transaction');
		$transaction->load($_GET['transaction_id']);
		
		
		
		$cols= $this->add('Columns');
		$left=$cols->addColumn(6);
		$right=$cols->addColumn(6);

		$left->add('View')->set(array('Transaction Date : ' . $transaction['created_at'],'icon'=>'calendar'));
		$right->add('View')->set(array($transaction['transaction_type'],'icon'=>'check'));
		$grid=$this->add('Grid');
		$grid->setModel($transaction->ref('TransactionRow')->setOrder('amountDr desc, amountCr desc'),array('account','amountDr','amountCr'));

		$this->add('View')->set(array($transaction['Narration'],'icon'=>'pencil'));
		$this->add('View')->set(array($transaction['reference'],'icon'=>'pencil'));


		if(!$_GET['hide_print_btn']){
			$btn=$this->add('Button')->set('print')->addClass('pull-right');
			if($btn->isClicked()){
				$this->js()->univ()->newWindow($this->api->url('voucher_print',array('transaction_id'=>$_GET['transaction_id'],'hide_print_btn'=>1,'cut_page'=>0)))->execute();
			}
		}

	}
}