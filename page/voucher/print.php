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

		if($_GET['transaction_id']){
			$vp = $this->add('View_VoucherPrint');
			$vp->transaction_id = $_GET['transaction_id'];
		}else{
			$selected_voucher_array = json_decode($_GET['selected_voucher_list']);
			foreach ($selected_voucher_array as $key => $voucher_id) {
				$transaction_model = $this->add('Model_Transaction')->load($voucher_id);
				$vp = $this->add('View_VoucherPrint');
				$vp->transaction_id = $transaction_model->id;
			}
		}
	}
}