<?php

class page_transactions_remove extends Page {
	function init(){
		parent::init();

		$voucher_vp = $this->add('VirtualPage')->set(function($p){
			$f_year = $p->api->getFinancialYear($p->api->today);	
			$start_date = $f_year['start_date'];
			$end_date = $f_year['end_date'];

			$transaction = $p->add('Model_Transaction');
			$transaction->addCondition('voucher_no',$p->api->stickyGET('voucher_no'));
			$transaction->addCondition('created_at','>=',$start_date);
			$transaction->addCondition('created_at','<',$p->api->nextDate($end_date));
			$transaction->addCondition('branch_id',$p->api->stickyGET('branch_id'));
			$transaction->tryLoadAny();


			$v = $p->add('View_Voucher');
			$v->setModel($transaction);

			if($transaction->referenceAccount()->isRecurring()){
				$p->add('CRUD')->setModel($transaction->referenceAccount()->premiums());
			}else{
				// echo $transaction->referenceAccount()->get('AccountNumber');
			}

			$btn = $p->add('Button')->set("DELETE TRANSACTION");

			if($btn->isClicked()){
				try{
					$p->api->db->beginTransaction();
						$transaction->forceDelete();
				    $p->api->db->commit();
				} catch (Exception $e) {
				   	$p->api->db->rollBack();
				   	throw $e;
				}

				$p->js()->univ()->closeDialog()->execute();
			}

		});


		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','branch')->validateNotNull()->setModel('Branch');
		$form->addField('line','voucher_no')->validateNotNull();
		$form->addSubmit('Get Voucher');

		if($form->isSubmitted()){
			$this->js()->univ()->frameURL('TRANSACTION',$this->api->url($voucher_vp->getURL(),array('branch_id'=>$form['branch'],'voucher_no'=>$form['voucher_no'])))->execute();
		}

	}
}