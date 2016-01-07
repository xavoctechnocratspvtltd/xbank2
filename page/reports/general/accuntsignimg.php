<?php

class page_reports_general_accuntsignimg extends Page {
	public $title="Account Signature Image";
	function init(){
		parent::init();
		$account_model=$this->add('Model_Account',array('table_alias'=>'acc'));
		
		$form = $this->add('Form');
		$account_field = $form->addField('autocomplete/Basic',array('name'=>'account'))->validateNotNull();

		$account_field->setModel($account_model,'AccountNumber');
		$form->addSubmit('submit');
		$view=$this->add('View');

		$this->api->stickyGET('filter');
		$this->api->stickyGET('account');

		if($_GET['filter']){

			$account = $this->add('Model_Account')->tryLoadBy('AccountNumber',$_GET['account']);
			if($account->loaded()){
				$view->add('H3')->set(array('Signature For - '));
				$view->add('View')->set($_GET['account']);
				$view->add('View')->setHtml('Account Mode: ' . $account['ModeOfOperation'] . ($account['ModeOfOperation'] == 'Joint'? '<font color=red> Check All Signatures </font>':''));
				// $img=$right_col->add('View')->setElement('img')->setAttr('src','../signatures/sig_'.$account->ref('member_id')->get('id').'.JPG');
				$img=$view->add('View')->setElement('img')->setAttr('src',$account['sig_image'])->setStyle('width','50%');
				// $img->js('mouseover',$img->js()->width('100%'));
				// $img->js('mouseout',$img->js()->width('50%'));
				$account_field->other_field->set($_GET['account']);
				$account_field->set($account->id);
			}
		}else{
			$view->add('View_Error')->set('Select Account for Show Signature');
		}

		if($form->isSubmitted()){
			$view->js()->reload(
								array('account'=>$form['account'],
									'filter'=>1))->execute();
		}
		
	}

}
