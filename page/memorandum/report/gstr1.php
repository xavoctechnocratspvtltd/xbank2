<?php

class page_memorandum_report_gstr1 extends Page{
	public $title = "GSTR-1";

	function init(){
		parent::init();

		$this->filter = $this->app->stickyGET('filter')?:0;
		$this->from_date = $this->app->stickyGET('from_date')?:0;
		$this->to_date = $this->app->stickyGET('to_date')?:0;

		$form = $this->add('Form',null,null,['form/horizontal']);
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addSubmit('Filter');
		$form->add('misc\Controller_FormAsterisk');
		$this->add('View')->setElement('hr');

		$data_array = [];
		if($this->filter){
			$data_array = $this->getData();
		}
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


		$grid->add('VirtualPage')
		->addColumn('detail')
		->set(function($page){
			$id = $_GET[$page->short_name.'_id'];
			$page->add('Text')->set('ID='.$id);
			$page->add('Text')->set('From ='.$this->from_date);
			$page->add('Text')->set('To ='.$this->to_date);
		});

		if($form->isSubmitted()){
			$grid->js()->reload(['filter'=>1,'from_date'=>$form['from_date'],'to_date'=>$form['to_date']])->execute();
		}

	}


	function getData(){

		$all_gst = [
			'GST 18'=>[],		
			'GST 28'=>[],
			'GST 5'=>[],
			'GST 6'=>[]
		];
		$data_array = [];

		foreach ($all_gst as $gst_name => $value) {
			$percent = explode(" ", $gst_name)[1];

			$gst_array = [
					$this->api->currentBranch['Code'].SP.'SGST '.($percent/2).'%',
					$this->api->currentBranch['Code'].SP.'CGST '.($percent/2).'%',
					$this->api->currentBranch['Code'].SP.'IGST '.$percent.'%'
				];

			$model_tra = $this->add('Model_GST_Transaction',['gst_array'=>$gst_array]);

			$model_tra->addCondition('created_at','>=',$this->from_date);
			$model_tra->addCondition('created_at','<',$this->app->nextDate($this->to_date));
			$model_tra->addCondition('branch_id',$this->app->currentBranch->id);

			if(!$model_tra->count()->getOne()) continue;

			$all_transaction = $model_tra->getRows(['cr_sum','dr_sum','taxable_value','igst','sgst','cgst','tax_amount_sum']);
			
			// echo "<pre>";
			// print_r($all_transaction);
			// echo "</pre>";

			$temp = ['id'=>$gst_name,'taxable_value'=>0,'igst'=>0,'cgst'=>0,'sgst'=>0,'total_tax'=>0,'total_value'=>0];
			foreach ($all_transaction as $key => $value) {
				$temp = [
					'id'=>$gst_name,
					'taxable_value'=>($temp['taxable_value']+$value['taxable_value']),
					'igst'=>$temp['igst'],
					'cgst'=>($temp['cgst']+$value['cgst']),
					'sgst'=>($temp['sgst']+$value['sgst']),
					'total_tax'=>($temp['total_tax']+$value['tax_amount_sum']),
					'total_value'=>($temp['total_value']+$value['dr_sum'])
				];
			}

			if(count($temp) && $temp['taxable_value'] > 0) $data_array[] = $temp;
		}

		return $data_array;
	}
}