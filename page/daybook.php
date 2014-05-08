<?php

class page_daybook extends Page {
	public $voucher_no=0;

	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','date')->validateNotNull();
		$form->addSubmit('Open Day Book');

		$day_transaction_model = $this->add('Model_Transaction');
		$transaction_row=$day_transaction_model->join('transaction_row.transaction_id');
		$transaction_row->hasOne('Account','accounts_id');
		$transaction_row->addField('amountDr');
		$transaction_row->addField('amountCr');

		$day_transaction_model->add('Controller_Acl');
		
		$daybook_lister_grid = $this->add('Lister_DayBook');
 
		$daybook_lister_grid->setModel($day_transaction_model,array('voucher_no','Narration','accounts','amountDr','amountCr'));
		$daybook_lister_grid->removeColumn('Narration');

		$daybook_lister_grid->addPaginator(10);



		if($form->isSubmitted()){
			$daybook_lister_grid->js()->reload(array('date_selected'=>$form['date']))->execute();
		}
	}
}