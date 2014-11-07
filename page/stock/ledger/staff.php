<?php	
class page_stock_ledger_staff extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item')->setEmptyText('Please Select');
		$item_field->setModel('Stock_Item');
		$staff_field=$form->addField('dropdown','staff')->validateNotNull()->setEmptyText('Please Select');
		$staff_field->setModel('Stock_Staff');
 
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('GET');
		$v=$this->add('View');

		$v->add('H4')->set('Staff Consume Leadger');
		$view_consume=$v->add('View_StockMember_Consume',array('member'=>$_GET['staff'],'from_date'=>$_GET['from_date'],'to_date'=>$form['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));
		
		$v->add('H4')->set('Staff Issue Leadger');
		$view_issue=$v->add('View_StockMember_Issue',array('item'=>$_GET['item'],'member'=>$_GET['staff'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));

		$v->add('H4')->set('Staff FixedAssets Leadger');
		$view_fixed=$v->add('View_StockMember_FixedAssets',array('member'=>$_GET['staff'],'from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'filter'=>$_GET['filter'],'type'=>'Staff'));

		if($form->isSubmitted()){
			$v->js()->reload(array('item'=>$form['item'],'staff'=>$form['staff'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'filter'=>1))->execute();
		}

	}
}