<?php

class page_members extends Page {
	
	public $title='Member Management';

	function init(){
		parent::init();

		$member_model = $this->add('Model_Member');

		$member_model->addCondition('branch_id',$this->api->current_branch->id);

		$crud = $this->add('CRUD');
		$crud->setModel($member_model);
		if($g= $crud->grid) {
			$g->addMethod('format_removeEdit',function($grid,$field){
				if($grid->model['name'] == $grid->model->ref('branch_id')->get('Code').SP.'Default')
					$grid->current_row_html[$field]='';
			});
			$g->addMethod('format_removeDelete',function($grid,$field){
				if($grid->model['name'] == $grid->model->ref('branch_id')->get('Code').SP.'Default')
					$grid->current_row_html[$field]='';
			});
			$g->addFormatter('edit','removeEdit');
			$g->addFormatter('delete','removeDelete');
			$g->addPaginator(10);
		}
	}
}