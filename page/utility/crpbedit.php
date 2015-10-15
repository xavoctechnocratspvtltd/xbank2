<?php
class page_utility_crpbedit extends Page{
	function init(){
		parent::init();

		$crud = $this->add('CRUD',array('allow_add'=>false,'allow_del'=>false));
		$crud->setmodel('Agent',array('code','name','current_individual_crpb','updated_at'));
		if(!$crud->isEditing()){
			$g=  $crud->grid;

			$g->addPaginator(100);

			$g->addFormatter('current_individual_crpb','grid/inline');

			$g->addQuickSearch(array('name'));
			
		}

	}
}