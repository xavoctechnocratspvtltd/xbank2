<?php

class page_reports_daybook extends Page {
	public $title = "Day Book ";
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','date')->validateNotNull();
		$form->addSubmit('Open Day Book');

		$day_transaction_model = $this->add('Model_Transaction');
		$transaction_row=$day_transaction_model->join('transaction_row.transaction_id');
		$transaction_row->hasOne('Account','account_id');
		$transaction_row->addField('amountDr');
		$transaction_row->addField('amountCr');

		$day_transaction_model->add('Controller_Acl');
		$day_transaction_model->setOrder('voucher_no');
		
		$daybook_lister_grid = $this->add('Grid_DayBook');

		if($_GET['date_selected']){
			$day_transaction_model->addCondition('created_at','>=',$_GET['date_selected']);
			$day_transaction_model->addCondition('created_at','<',$this->api->nextDate($_GET['date_selected']));
		}else{
			$day_transaction_model->addCondition('created_at','>=',$this->api->today);
			$day_transaction_model->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		}
 
		$daybook_lister_grid->setModel($day_transaction_model,array('voucher_no','Narration','account','amountDr','amountCr'));
		$daybook_lister_grid->removeColumn('Narration');
		// $daybook_lister_grid->addPaginator(10);



		if($form->isSubmitted()){
			$daybook_lister_grid->js()->reload(array('date_selected'=>$form['date']?:0))->execute();
		}
	}
}