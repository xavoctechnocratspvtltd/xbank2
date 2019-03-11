<?php
class page_utility_odlimit extends Page{
	public $title ="OD Limit";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		$this->app->stickyGET('selected_od_bank_account');

		$od_bank_account_model = $this->add('Model_Active_Account');
		$od_bank_account_model->addCondition('scheme','Bank OD');

		$form = $this->add('Form');
		$od_field = $form->addField('DropDown','od_bank_account','OD Bank Account');
		$od_field->setModel($od_bank_account_model);
		$od_field->setEmptyText('Please Select OD Bank Account');
		$od_field->validateNotNull();
		
		$od_limit_field = $form->addField('Number','od_account_limit');
		if($od_id = $_GET['selected_od_bank_account']){
			$model = $this->add('Model_Active_Account');
			$model->load($od_id);
			if($model->loaded() AND $model['scheme'] == "Bank OD"){
				$od_limit_field->set($model['bank_account_limit']?:0);
			}
		}else{
			$od_limit_field->set(0);
		}
		$od_limit_field->validateNotNull();
		$form->addSubmit('Update OD Limit');

		$od_field->js('change',$form->js()->atk4_form('reloadField','od_account_limit',array($this->api->url(),'selected_od_bank_account'=>$od_field->js()->val())));

		if($form->isSubmitted()){
			$model = $this->add('Model_Active_Account');
			$model->load($form['od_bank_account']);
			$model['bank_account_limit'] = $form['od_account_limit'];
			$model->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('OD Account Limit Updated')->execute();
		}

	}
}