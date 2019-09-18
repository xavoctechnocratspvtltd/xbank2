<?php

class page_reports_telecalleraccountassociation extends Page {
	public $title="TeleCaller Account Association";

	function init(){
		parent::init();
		
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$telecaller_id = $this->app->stickyGET('telecaller_id');

		$form = $this->add('Form');
		$field_telecaller = $form->addField('autocomplete/Basic','telecaller');
		$field_telecaller->setModel('TeleCaller');
		// $field_mo->validateNotNull();

		$form->addField('DatePicker','from_date')->set($from_date);
		$form->addField('DatePicker','to_date')->set($to_date);
		$form->addSubmit('Filter');

		$model = $this->add('Model_TeleCallerAccountHistory');

		if($telecaller_id){
			$model->addCondition('telecaller_id',$telecaller_id);
		}
		if($to_date){
			$model->addCondition('from_date','<',$this->app->nextDate($to_date));
		}
		if($from_date){
			$model->addCondition('to_date','>',$this->app->previousDate($from_date));
		}

		$crud = $this->add('CRUD',['allow_add'=>false]);
		$crud->grid->setModel($model);
		$crud->grid->addFormatter('account','WRAP');
		$crud->add('Controller_Acl');

		$crud->grid->removeColumn('final_to_date');
		$crud->grid->addPaginator(500);
		if($form->isSubmitted()){
			$crud->js()->reload(array(
								'telecaller_id'=>$form['telecaller'],
								'from_date'=>$form['from_date']?:0,
								'to_date'=>$form['to_date']?:0
							))->execute();
		}


	}
}