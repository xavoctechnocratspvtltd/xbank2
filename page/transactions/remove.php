<?php

class page_transactions_remove extends Page {
	public $title="Edit/Delete Voucher";

	function init(){
		parent::init();

		$this->add('Controller_Acl',array('default_view'=>false));

		$columns = $this->add('Columns');

		$remove_col= $columns->addColumn(6);
		$edit_col= $columns->addColumn(6);


		// ================  DELETE VOUCHER =====================

		$delete_voucher_vp = $this->add('VirtualPage')->set(function($p){
			$f_year = $p->api->getFinancialYear($p->api->today);	
			$start_date = $f_year['start_date'];
			$end_date = $f_year['end_date'];

			$transaction = $p->add('Model_Transaction');
			$transaction->addCondition('voucher_no',$p->api->stickyGET('voucher_no'));
			$transaction->addCondition('created_at','>=',$start_date);
			$transaction->addCondition('created_at','<',$p->api->nextDate($end_date));
			$transaction->addCondition('branch_id',$p->api->stickyGET('branch_id'));
			$transaction->tryLoadAny();

			if(!$transaction->loaded()){
				$p->add('View_Error')->set('Could Not Load Such Transaction');
				return;
			}

			$v = $p->add('View_Voucher');
			$v->setModel($transaction);

			if($transaction->referenceAccount()->isRecurring()){
				$p->add('CRUD')->setModel($transaction->referenceAccount()->premiums());
			}else{
				// echo $transaction->referenceAccount()->get('AccountNumber');
			}

			if($p->api->auth->model->isSuper()){

				$form = $p->add('Form');
				$form->addField('line','confirm_amount');
				$form->addSubmit('Delete');

				if($form->isSubmitted()) {
					
					if($form['confirm_amount'] != $transaction['cr_sum'])
						$form->displayError('confirm_amount','Amount Mismatched');

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

			}

		});

		$remove_col->add('H3')->set('Delete Voucher');
		$form = $remove_col->add('Form');
		$form->addField('autocomplete/Basic','branch')->validateNotNull()->setModel('Branch');
		$form->addField('line','voucher_no')->validateNotNull();
		$form->addSubmit('Get Voucher');

		if($form->isSubmitted()){
			$this->js()->univ()->frameURL('TRANSACTION',$this->api->url($delete_voucher_vp->getURL(),array('branch_id'=>$form['branch'],'voucher_no'=>$form['voucher_no'])))->execute();
		}

		// ================  EDIT VOUCHER =====================

		$edit_voucher_vp = $this->add('VirtualPage')->set(function($p){
			$p->add('View_Error')->set('Not Working');
			return;
			$f_year = $p->api->getFinancialYear($p->api->today);	
			$start_date = $f_year['start_date'];
			$end_date = $f_year['end_date'];

			$transaction = $p->add('Model_Transaction');
			$transaction->addCondition('voucher_no',$p->api->stickyGET('voucher_no'));
			$transaction->addCondition('created_at','>=',$start_date);
			$transaction->addCondition('created_at','<',$p->api->nextDate($end_date));
			$transaction->addCondition('branch_id',$p->api->stickyGET('branch_id'));
			$transaction->tryLoadAny();

			if(!$transaction->loaded()){
				$p->add('View_Error')->set('Could Not Load Such Transaction');
				return;
			}

			$form = $p->add('Form_Stacked');
			$form->add('H2')->set('Debit');
			$credit_start=false;
			$i=1;
			foreach ($transaction->rows()->setOrder('amountDr desc') as $r) {
				if($r['amountCr']!='0' and !$credit_start){
					$form->add('H2')->set('Credit');
					$credit_start=true;
				}
				$form->addField('line','tr_'.$i,$r['account'])->set(($r['amountCr']!=0)?$r['amountCr']:$r['amountDr']);
				$i++;
			}

			$form->addSubmit('UPDATE');

			if($transaction['transaction_type'] == TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT){
				$p->add('CRUD',array('allow_add'=>false,'allow_del'=>false))->setModel('Premium')->addCondition('account_id',$transaction['reference_id']);
			}

			if($form->isSubmitted()){
				try{
						$p->api->db->beginTransaction();
						$i=1;
						$cr_sum=0;
						$dr_sum=0;
						foreach ($transaction->rows()->setOrder('amountDr desc') as $r) {
							if($r['amountCr']!=0){
								$cr_sum += ($r['amountCr'] = $form['tr_'.$i]);
							}else{
								$dr_sum += ($r['amountDr'] = $form['tr_'.$i]);
							}
							$r->save();
							$i++;
						}
						if($dr_sum != $cr_sum){
						   	$p->api->db->rollBack();
							$form->js()->univ()->errorMessage('Debit Must Be equal to Credit Amount')->execute();
						}
					    $p->api->db->commit();
					} catch (Exception $e) {
					   	$p->api->db->rollBack();
					   	throw $e;
					}


				$form->js()->univ()->successMessage('Updated')->execute();
			}


		});

		$edit_col->add('H3')->set('Edit Voucher');
		$form = $edit_col->add('Form');
		$form->addField('autocomplete/Basic','branch')->validateNotNull()->setModel('Branch');
		$form->addField('line','voucher_no')->validateNotNull();
		$form->addSubmit('Get Voucher');

		if($form->isSubmitted()){

			$this->js()->univ()->frameURL('TRANSACTION',$this->api->url($edit_voucher_vp->getURL(),array('branch_id'=>$form['branch'],'voucher_no'=>$form['voucher_no'])))->execute();
		}

	}
}