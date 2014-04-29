<?php

class page_index extends Page{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
		$crud=$this->add('CRUD');
		$crud->setModel('Staff');

		if($crud->isEditing() and $crud->form->isSubmitted() ){
			$x=$this->add('Model_Branch');
			$x['name']='temp';
			$x->save();
		}

		if($crud->grid)
			$crud->grid->addPaginator(5);

	}
}