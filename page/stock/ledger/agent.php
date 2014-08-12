<?php	
class page_stock_ledger_Agent extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$staff_field=$form->addField('dropdown','agent')->setEmptyText('Please Select');
		$staff_field->setModel('Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('Get');
		$v=$this->add('View');
		$view_consume=$v->add('View_Agent_Consume',array('agent'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>1));
		$view_issue=$v->add('View_Agent_Issue',array('agent'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']));

		$view_fixed=$v->add('View_Agent_FixedAssets',array('agent'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']));

		if($form->isSubmitted()){
			$v->js()->reload(array('agent'=>$form['agent'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}


	}
}