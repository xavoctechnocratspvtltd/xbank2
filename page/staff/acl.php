<?php

class page_staff_acl extends Page {
	public $title = "Staff ACL Management";

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$data_tab = $tabs->addTab('Data');
		$report_tab = $tabs->addTab('Report');
		$document_tab = $tabs->addTab('Document');

		$acl_model = $data_tab->add('Model_Acl');
		$acl_model->addCondition('documents_id',null);
		if($sid=$data_tab->api->stickyGET('staff_id')){
			$acl_model->addCondition('staff_id',$sid);
		}

		$acl_model->setOrder('class,staff_id');

		$crud = $data_tab->add('CRUD');
		$crud->setModel($acl_model,['staff_id','class','can_view','allow_add','allow_edit','allow_del','is_all_branch_allowed'],['staff','class','can_view','allow_add','allow_edit','allow_del','is_all_branch_allowed']);
		$crud->grid->addPaginator(200);
		$crud->grid->addQuickSearch(['staff','class']);

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


		// Document ACL Management
		$document_acl_crud = $document_tab->add('CRUD');
		$acl_model = $this->add('Model_DocumentAcl');
		
		if($sid=$data_tab->api->stickyGET('staff_id')){
			$acl_model->addCondition('staff_id',$sid);
		}
		$document_acl_crud->setModel($acl_model,['staff_id','documents_id','class','allow_edit','allow_del'],['documents','staff','class','allow_edit','allow_del']);

	}
}