<?php

class page_daybook extends Page {
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','date')->validateNotNull();
		$form->addSubmit('Open Day Book');

		$day_transaction_model = $this->add('Model_Transaction');
		$day_transaction_model->setLimit(10);
		$day_transaction_model->addExpression('name')->set('Narration');
		
		$daybook_lister = $this->add('Lister_DayBook');
		$daybook_lister->setModel($day_transaction_model);
		// $p=$daybook_lister->add('Paginator');
		// $p->ipp = 10;

		if($form->isSubmitted()){
			$daybook_lister->js()->reload(array('date_selected'=>$form['date']))->execute();
		}
	}
}