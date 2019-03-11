<?php

class Model_DocumentAcl extends Model_Acl {

	function init(){
		parent::init();

		$this->addCondition('documents_id','<>',null);
		$this->addCondition('class','Model_DocumentSubmitted');
	}
}