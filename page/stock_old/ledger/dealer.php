<?php	
class page_stock_ledger_Dealer extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$staff_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$staff_field->setModel('Dealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('Get');
		$v=$this->add('View');
		$view_consume=$v->add('View_Dealer_Consume',array('dealer'=>$_GET['dealer'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>1));
		$view_issue=$v->add('View_Dealer_Issue',array('agent'=>$_GET['dealer'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']));

		$view_fixed=$v->add('View_Dealer_FixedAssets',array('dealer'=>$_GET['dealer'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']));

		if($form->isSubmitted()){
			$v->js()->reload(array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}


	}
}