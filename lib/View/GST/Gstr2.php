<?php

class View_GST_Gstr2 extends View {
	public $from_date;
	public $to_date;

	function init(){
		parent::init();

		if(!$this->from_date && !$this->to_date) {
			$this->add('View')->set('From & To Date are mandatory');
			return;
		}

		$data_array = $this->add('Model_GST_Transaction')->getOutwardGSTData($this->from_date,$this->to_date);

		// echo "<pre>";
		// print_r($data_array);
		// echo "</pre>";

		$grid = $this->add('Grid');
		$grid->setSource($data_array);
		$grid->addColumn('taxable_value');
		$grid->addColumn('igst');
		$grid->addColumn('cgst');
		$grid->addColumn('sgst');
		$grid->addColumn('total_tax');
		$grid->addColumn('total_value');

		$vp = $grid->add('VirtualPage');
		$vp->addColumn('detail','Detail');
		$vp->set([$this,'showDetail']);
	}

	function showDetail($page){
		$id = $_GET[$page->short_name.'_id'];
		// $page->add('Text')->set('ID ='.$id);
		// $page->add('Text')->set('From ='.$this->from_date);
		// $page->add('Text')->set('To ='.$this->to_date);
		$percentage = explode(" ", $id)[1];
		$gst_array = [
			'sgst'=>$this->api->currentBranch['Code'].SP.'SGST '.($percentage/2).'%',
			'cgst'=>$this->api->currentBranch['Code'].SP.'CGST '.($percentage/2).'%',
			'igst'=>$this->api->currentBranch['Code'].SP.'IGST '.$percentage.'%'
		];
		// $tra_array = [TRA_PURCHASE_ENTRY];

		$tra = $this->add('Model_GST_Transaction',['gst_array'=>$gst_array,'tax_field'=>'amountCr','gst_report'=>'outward']);
		$tra->addCondition('created_at','>=',$this->from_date);
		$tra->addCondition('created_at','<',$this->app->nextDate($this->to_date));
		$tra->addCondition('is_sale_invoice',1);
		$tra->addCondition('transaction_type','<>',[TRA_PURCHASE_ENTRY]);
		if($this->app->currentBranch->id)
			$tra->addCondition('branch_id',$this->app->currentBranch->id);

		$tra->getElement('created_at')->caption('Date');
		$tra->getElement('created_at')->caption('Date');
		$tra->getElement('tax_amount_sum')->caption('Total Tax');
		$tra->getElement('cr_sum')->caption('Total Invoice Value');

		$grid = $page->add('Grid');
		$grid->setModel($tra,['created_at','reference','transaction_type','gstin','voucher_no','invoice_no','taxable_value','igst','cgst','sgst','tax_amount_sum','cr_sum']);
		$grid->addPaginator(100);
		$grid->addTotals(['taxable_value','igst','cgst','sgst','tax_amount_sum','cr_sum']);
	}
}