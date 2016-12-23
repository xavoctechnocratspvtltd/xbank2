<?php

class Controller_DocumentsManager extends AbstractController{
	public $doc_type=null;
	
	function init(){
		parent::init();

		$grid=null;

		if(($this->owner instanceof CRUD) or ($this->owner instanceof xCRUD)){
			$grid = $this->owner->grid;
		}
		if($this->owner instanceof Grid){
			$grid = $this->owner;
		}

		if(!$grid)
			throw $this->exception('Add DocumentsManager on CRUD or grid only');

		$grid->addColumn('Expander','documents_manager',array('page'=>'documents_manager&what='.$this->doc_type,'descr'=>'Documents','icon'=>'docs','icon_only'=>true));


	}
}