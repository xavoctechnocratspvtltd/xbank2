<?php

class Controller_Acl extends AbstractController{
	
	function init(){
		parent::init();

		if($this->api->auth->model['AccessLevel'] <= 80)
			$this->owner->addCondition('branch_id',$this->api->current_branch->id);

	}
}