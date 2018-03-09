<?php

class page_staff_acl extends Page {
	public $title = "Staff ACL Management";

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$data_tab = $tabs->addTab('Data');
		$report_tab = $tabs->addTab('Report');

		$acl_model = $data_tab->add('Model_Acl');

		if($sid=$data_tab->api->stickyGET('staff_id')){
			$acl_model->addCondition('staff_id',$sid);
		}

		$acl_model->setOrder('class,staff_id');

		$crud = $data_tab->add('CRUD');
		$crud->setModel($acl_model);

		if(!$crud->isEditing()){
			$crud->grid->addFormatter('can_view','grid/inline');
			$crud->grid->addFormatter('is_all_branch_allowed','grid/inline');
			$crud->grid->addFormatter('allow_add','grid/inline');
			$crud->grid->addFormatter('allow_edit','grid/inline');
			$crud->grid->addFormatter('allow_del','grid/inline');
		}


		$m = $report_tab->add('Model_StaffReportAcl');
		if($sid=$data_tab->api->stickyGET('staff_id')){
			$m->addCondition('staff_id',$sid);
		}
		$m->setOrder('staff_id,page');

		$crud = $report_tab->add('CRUD',['allow_add'=>false]);
		$crud->setModel($m);

		$crud->grid->addQuickSearch(['staff','page']);
		$crud->grid->addPaginator(200);

		if(!$crud->isEditing()){
			$crud->grid->addFormatter('is_allowed','grid/inline');
		}
	}


}