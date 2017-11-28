<?php
class Model_Document extends Model_DocumentAll {	
	function init(){
		parent::init();

		$this->addCondition('is_active',true);

	}
}