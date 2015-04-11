<?php

class page_staff_acl extends Page {
	public $title = "Staff ACL Management";

	function init(){
		parent::init();

		$acl_model = $this->add('Model_Acl');

		if($sid=$this->api->stickyGET('staff_id')){
			$acl_model->addCondition('staff_id',$sid);
		}

		$acl_model->setOrder('class,staff_id');

		$crud = $this->add('CRUD');
		$crud->setModel($acl_model);

		if(!$crud->isEditing()){
			$crud->grid->addFormatter('can_view','grid/inline');
			$crud->grid->addFormatter('is_all_branch_allowed','grid/inline');
			$crud->grid->addFormatter('allow_add','grid/inline');
			$crud->grid->addFormatter('allow_edit','grid/inline');
			$crud->grid->addFormatter('allow_del','grid/inline');
		}

	}


}