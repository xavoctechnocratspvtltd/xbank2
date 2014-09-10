<?php

class page_stock_reports_genral extends Page{
	function init(){
		parent::init();

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','type')->setValueList(array('Purchase','Issue','Consume','Submit','PurchaseReturn','Dead','Transfer','Openning','Sold'));
		$staff_field=$form->addField('autocomplete/Basic','staff');
		$staff_field->setModel('Staff');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$dealer_field=$form->addField('autocomplete/Basic','dealer');
		$dealer_field->setModel('Dealer');
		$form->addSubmit('GET LIST');

		$grid=$this->add('Grid');
		$transaction=$this->add('Model_Stock_Transaction');

		if($_GET['filter']){

			if($_GET['staff'])
				$transaction->addCondition('staff_id',$_GET['staff']);
			if($_GET['agent'])
				$transaction->addCondition('agent_id',$_GET['agent']);
			if($_GET['dealer'])
				$transaction->addCondition('dealer_id',$_GET['dealer']);
			if($_GET['from_date'])
				$transaction->addCondition('dealer_id',$_GET['dealer']);

		}

		if($form->isSubmitted()){

			$selected=0;
			if(!$selected and $form['staff'])
					$selected=$form['staff'];
			if($form['agent']){
				if($selected)
					$form->displayError('agent','Please Selecet One');
				else
					$selected=$form['agent'];
			}

			if($form['dealer']){
				if($selected)
					$form->displayError('dealer','Please Selecet One');
				else
					$selected=$form['dealer'];
			}

			$form->js()->reload(array('filter'=>1,'from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'staff'=>$form[$selected],'agent'=>$form[$selected],'dealer'=>$form[$selected]))->execute();
		}



		

	}
}