<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
		$crud=$this->add('CRUD');
		$crud->setModel('Document');

		if($crud->isEditing() and $crud->form->isSubmitted() ){
			$x=$this->add('Model_Branch');
			$x['name']='temp';
			$x->save();
		}

		if($crud->grid)
			$crud->grid->addPaginator(5);

	}
}