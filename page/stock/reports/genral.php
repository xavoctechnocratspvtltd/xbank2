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
			if($_GET['type'])
			$transaction->addCondition('transaction_type',$_GET['type']);

			if($_GET['type']=='Issue'){

				if($_GET['from_date'])
					$transaction->addCondition('issue_date','<',$_GET['from_date']);
				if($_GET['to_date'])
					$transaction->addCondition('issue_date','>',$_GET['to_date']);
			}
			elseif($_GET['type']=='Issue'){
				if($_GET['from_date'])
					$transaction->addCondition('submit_date','<',$_GET['from_date']);
				if($_GET['to_date'])
					$transaction->addCondition('submit_date','>',$_GET['to_date']);
			}else{
				if($_GET['from_date'])
					$transaction->addCondition('created_at','<',$_GET['from_date']);
				if($_GET['to_date'])
					$transaction->addCondition('created_at','<',$_GET['to_date']);
			}


		}

		$grid->setModel($transaction);

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

			$grid->js()->reload(array('filter'=>1,'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'staff'=>$form['staff'],'agent'=>$form['agent'],'dealer'=>$form['dealer'],'type'=>$form['type']))->execute();
		}

	}
}