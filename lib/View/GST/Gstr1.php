<?php

class View_GST_Gstr1 extends View {
	public $from_date;
	public $to_date;

	function init(){
		parent::init();

		if(!$this->from_date && !$this->to_date) {
			$this->add('View')->set('From & To Date are mandatory');
			return;
		}


		$data_array = $this->add('Model_GST_Transaction')->getInwardGSTData($this->from_date,$this->to_date);
		// echo "<pre>";
		// print_r($temp);
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
		$page->add('View')->set($id);

		$tax = explode(" ", $id)[1];
		$tax_half = ($tax/2);

		$gst_array = [
			'sgst'=>$this->api->currentBranch['Code'].SP.'SGST '.$tax_half."%",
			'cgst'=>$this->api->currentBranch['Code'].SP.'CGST '.$tax_half."%",
			'igst'=>$this->api->currentBranch['Code'].SP.'IGST '.$tax."%"
		];
		// $page->add('Text')->set('From ='.$this->from_date);
		// $page->add('Text')->set('To ='.$this->to_date);

		$tra = $this->add('Model_GST_Transaction',['gst_array'=>$gst_array,'tax_field'=>'amountDr','gst_report'=>'inward']);
		if($this->app->currentBranch->id)
			$tra->addCondition('branch_id',$this->app->currentBranch->id);
		$tra->addCondition('transaction_type',TRA_PURCHASE_ENTRY);
		$tra->addCondition('created_at','>=',$this->from_date);
		$tra->addCondition('created_at','<',$this->app->nextDate($this->to_date));

		$tra->getElement('created_at')->caption('Date');
		$tra->getElement('created_at')->caption('Date');
		$tra->getElement('tax_amount_sum')->caption('Total Tax');
		$tra->getElement('cr_sum')->caption('Total Invoice Value');

		$grid = $page->add('Grid');
		$grid->setModel($tra,['created_at','reference','transaction_type','gstin','voucher_no','taxable_value','igst','cgst','sgst','tax_amount_sum','cr_sum']);
		$grid->addPaginator(100);
		$grid->addTotals(['taxable_value','igst','cgst','sgst','tax_amount_sum','cr_sum']);
	}
}