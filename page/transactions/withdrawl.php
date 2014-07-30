<?php

class page_transactions_withdrawl extends Page {
	public $title ='WithDrawl Amount';
	
	function init(){
		parent::init();

		$cols= $this->add('Columns');
		$left_col = $cols->addColumn(6);
		$right_col = $cols->addColumn(6);
		$form = $left_col->add('Form');
		$account_field = $form->addField('autocomplete/Basic',array('name'=>'account'))->validateNotNull();
		$account_field->setModel('Account','AccountNumber');

		$form->addField('Number','amount')->validateNotNull();
		$form->addField('autocomplete/Basic','account_to_credit')->setFieldHint('sdfsd')->setModel('Account','AccountNumber');
		$form->addField('Text','narration');
		$form->addSubmit('Withdrawl');

		if($_GET['account_selected']){
			$account = $this->add('Model_Account')->tryLoadBy('AccountNumber',$_GET['account_selected']);
			if($account->loaded()){
				$right_col->add('H3')->set(array('Signature For - '));
				$right_col->add('View')->set($_GET['account_selected']);
				$img=$right_col->add('View')->setElement('img')->setAttr('src','../signatures/sig_'.$account->ref('member_id')->get('id').'.JPG');
				$img->js('mouseover',$img->js()->width('200%'));
				$img->js('mouseout',$img->js()->width('100%'));
				$account_field->other_field->set($_GET['account_selected']);
				$account_field->set($account->id);
			}
		}else{
			$right_col->add('View_Error')->set('Select Account for withdral');
		}


		$js=array(
				$right_col->js()->reload(array('account_selected'=>$account_field->js()->val())),
			);
		$account_field->other_field->js('change',$js);
		$account_field->js('change',$js);

		if($form->isSubmitted()){
			try{		
				$account_model_temp = $this->add('Model_Account')
											->loadBy('AccountNumber',$form['account']);

				if(!$account_model_temp->loaded())
					$form->displayError('amount','Oops');

				$account_model = $this->add('Model_Account_'.$account_model_temp->ref('scheme_id')->get('SchemeType'));
				$account_model->loadBy('AccountNumber',$form['account']);

				try {
					$this->api->db->beginTransaction();
				    $account_model->withdrawl($form['amount'],$form['narration'],$form['account_to_debit']?array(array($form['account_to_credit']=>$form['amount'])):array(),$form);
				    $this->api->db->commit();
				} catch (Exception $e) {
				   	$this->api->db->rollBack();
				   	throw $e;
				}
				$js=array($form->js()->reload(),$right_col->js()->reload());
				$form->js(null,$js)->univ()->successMessage($form['amount']."/- withdrawn from " . $form['account'])->execute();
			}catch(Exception_ValidityCheck $e){
				$form->displayError($e->getField(),$e->getMessage());
			}
		}
	}
}