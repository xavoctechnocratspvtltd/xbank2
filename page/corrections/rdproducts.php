<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

class page_corrections_rdproducts extends \Page {
	public $title='Corrections';

	function init(){
		parent::init();
		
		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addField('DatePicker','as_on_date')->validateNotNull();

		$form->addSubmit("Update");

		if($form->isSubmitted()){
			$account  = $this->add('Model_Active_Account_Recurring');
			$account->addCondition('created_at','>=',$form['from_date']);
			$account->addCondition('created_at','<',$this->api->nextDate($form['to_date']));

			foreach ($account as $acc) {
				$acc->ref('Premium')->_dsql()->set('Paid',0)->update();
				$acc->reAdjustPaidValue($form['as_on_date']);
				echo $acc['AccountNumber']. "done <br/>";
			}
		}

	}
}
