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


		$data_array = $this->add('Model_GST_Transaction')->getGSTData($this->from_date,$this->to_date);
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
		// $page->add('Text')->set('From ='.$this->from_date);
		// $page->add('Text')->set('To ='.$this->to_date);

		$tra = $this->add('Model_GST_Transaction');
		$tra->addCondition('branch_id',$this->app->currentBranch->id);
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