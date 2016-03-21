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
			// $account->addCondition('id',68522);

			foreach ($account as $acc) {
				$acc->ref('Premium')->_dsql()->set('Paid',0)->update();
				$dd = $this->api->my_date_diff($form['as_on_date'],$acc['created_at']);
				$months_total = $dd['months_total'];
				// echo $months_total. "<br/>";
				$date_carbon = new MyDateTime($acc['created_at']);
				// echo date('Y-m-t',strtotime($acc['created_at'])) . "<br/>";
				$acc->reAdjustPaidValue(date('Y-m-t',strtotime($acc['created_at'])));
				
				for($i=1;$i<=$months_total;$i++){
					$date_carbon = new MyDateTime($acc['created_at']);
					$date_carbon->add(new \DateInterval('P'.$i.'M'));
					// echo date('Y-m-t',strtotime($date_carbon->format('Y-m-d'))) . "<br/>";
					$acc->reAdjustPaidValue(date('Y-m-t',strtotime($date_carbon->format('Y-m-d'))));
				}
				// echo $form['as_on_date']."<br/>";

				// $acc->reAdjustPaidValue($form['as_on_date']);
				// var_dump($dd);
				// exit;

				echo $acc['AccountNumber']. "done <br/>";
			}
		}

	}
}
