<?php

class Model_DocumentAcl extends Model_ACL {

	function init(){
		parent::init();

		$this->addCondition('documents_id','<>',null);
		$this->addCondition('class','Model_DocumentSubmitted');
	}
}