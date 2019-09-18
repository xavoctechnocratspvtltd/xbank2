<?php

class page_reports_roaccountassociation extends Page {
	public $title="RO Account Association";

	function init(){
		parent::init();
		
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$mo_id = $this->app->stickyGET('mo_id');

		$form = $this->add('Form');
		$field_mo = $form->addField('autocomplete/Basic','mo','RO');
		$field_mo->setModel('Mo');
		// $field_mo->validateNotNull();

		$form->addField('DatePicker','from_date')->set($from_date);
		$form->addField('DatePicker','to_date')->set($to_date);
		$form->addSubmit('Filter');

		$model = $this->add('Model_MoAccountAssociation');

		if($mo_id){
			$model->addCondition('mo_id',$mo_id);
		}
		if($to_date){
			$model->addCondition('from_date','<',$to_date);
		}
		if($from_date){
			$model->addCondition('to_date','>',$from_date);
		}

		$crud = $this->add('CRUD',['allow_add'=>false]);
		$crud->grid->setModel($model);
		$crud->grid->addFormatter('account','WRAP');
		$crud->add('Controller_Acl');

		$crud->grid->removeColumn('_to_date');
		$crud->grid->addPaginator(500);
		if($form->isSubmitted()){
			$crud->js()->reload(array(
								'mo_id'=>$form['mo'],
								'from_date'=>$form['from_date']?:0,
								'to_date'=>$form['to_date']?:0
							))->execute();
		}


	}
}