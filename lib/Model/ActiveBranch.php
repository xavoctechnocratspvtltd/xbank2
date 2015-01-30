<?php

class Model_ActiveBranch extends Model_Branch{
	function init(){
		parent::init();

		$this->addCondition('published',true);
	}
}