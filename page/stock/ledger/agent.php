<?php	
class page_stock_ledger_Agent extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$staff_field=$form->addField('dropdown','agent')->validateNotNull()->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET');
		$v=$this->add('View');

		$v->add('H4')->set('Agent Consume Leadger');
		$view_consume=$v->add('View_StockMember_Consume',array('member'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Agent'));
		
		$v->add('H4')->set('Agent Issue Leadger');
		$view_issue=$v->add('View_StockMember_Issue',array('member'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Agent'));

		$v->add('H4')->set('Agent FixedAssets Leadger');
		$view_fixed=$v->add('View_StockMember_FixedAssets',array('member'=>$_GET['agent'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Agent'));
		
		if($form->isSubmitted()){
			$v->js()->reload(array('agent'=>$form['agent'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}


	}
}