<?php

class page_romoassociationedit extends Page {
	public $title="MO/RO Association Edit";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);

		$tabs = $this->add('Tabs');
		
		// MO Association Edit
		$mo_tab = $tabs->addTab('Mo Associations');
		$mo_form = $mo_tab->add('Form');
		$mo_form->addField('DropDown','mo')->setEmptyText("Please Select")->setModel('Mo');
		$mo_form->addSubmit('Filter');

		$mo_ass_model= $this->add('Model_MoAgentAssociation');
		$mo_ass_model->getElement('_to_date')->destroy();
		$mo_ass_model->getElement('to_date')->destroy();
		$mo_ass_model->addField('actual_to_date','_to_date')->type('datetime');
		$mo_ass_model->addExpression('effective_to_date')->set(function($m,$q){
			return $q->expr('IFNULL([0],"[1]")',[$m->getElement('actual_to_date'),$this->app->now]);
		});

		if($mo=$this->app->stickyGET('mo')){
			$mo_ass_model->addCondition('mo_id',$mo);
		}

		$crud = $mo_tab->add('CRUD');
		$crud->setModel($mo_ass_model,['mo_id','agent_id','from_date','actual_to_date'],['mo','agent','from_date','effective_to_date']);
		$crud->add('Controller_Acl',['default_view'=>false]);
		$crud->grid->addPaginator(100);

		if($mo_form->isSubmitted()){
			$crud->js()->reload(['mo'=>$mo_form['mo']])->execute();
		}
		
		$crud->add('Controller_Acl',['default_view'=>false]);

		// RO Association Edit
		$ro_tab = $tabs->addTab('Ro Associations');
		$ro_form = $ro_tab->add('Form');
		$ro_form->addField('DropDown','mo','RO')->setEmptyText("Please Select")->setModel('Mo');
		$ro_form->addSubmit('Filter');

		$ro_ass_model= $this->add('Model_MoAccountAssociation');
		$ro_ass_model->getElement('_to_date')->destroy();
		$ro_ass_model->getElement('to_date')->destroy();
		$ro_ass_model->addField('actual_to_date','_to_date')->type('datetime');
		$ro_ass_model->addExpression('effective_to_date')->set(function($m,$q){
			return $q->expr('IFNULL([0],"[1]")',[$m->getElement('actual_to_date'),$this->app->now]);
		});

		if($ro=$this->app->stickyGET('ro')){
			$ro_ass_model->addCondition('mo_id',$ro);
		}

		$crud = $ro_tab->add('CRUD');
		$crud->setModel($ro_ass_model,['mo_id','account_id','from_date','actual_to_date'],['mo','account','from_date','effective_to_date']);
		$crud->add('Controller_Acl',['default_view'=>false]);
		$crud->grid->addPaginator(100);
		$crud->add('Controller_Acl',['default_view'=>false]);
		if($ro_form->isSubmitted()){
			$crud->js()->reload(['ro'=>$ro_form['mo']])->execute();
		}


	}
}