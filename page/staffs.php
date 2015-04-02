<?php

class page_staffs extends Page {
	public $title = 'Staff Management';

	function init(){
		parent::init();


		$staff_model = $this->add('Model_Staff');
		// $staff_model->addCondition('branch_id',$this->api->current_branch->id);

		$staff_crud = $this->add('CRUD');
		$staff_crud->setModel($staff_model);

	}
}