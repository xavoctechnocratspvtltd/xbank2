<?php

class page_stock_reports_purchase extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET LIST');

		$purchase_tra=$this->add('Model_Stock_Transaction');
		$purchase_tra->addCondition('transaction_type','Purchase');

		$grid=$this->add('Grid_AccountsBase');

		if($_GET['filter']){
			if($_GET['from_date'])
				$purchase_tra->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$purchase_tra->addCondition('created_at','<=',$_GET['to_date']);
		}else
			$purchase_tra->addCondition('id',-1);
		$grid->setModel($purchase_tra,array('item','qty','rate','amount','created_at'));

		$grid->addSno();
		if($form->isSubmitted()){
			$grid->js()->reload(array('from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'filter'=>1))->execute();
		}

	}
}