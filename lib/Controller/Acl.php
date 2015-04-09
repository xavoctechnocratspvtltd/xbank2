<?php

class Controller_Acl extends AbstractController{
	
	function init(){
		parent::init();

		if($this->api->auth->model['AccessLevel'] <= 80){
			if($this->owner instanceof SQL_Model)
				$this->owner->addCondition('branch_id',$this->api->current_branch->id);
			elseif($this->owner instanceof CRUD or $this->owner instanceof Grid)
				$this->owner->model->addCondition('branch_id',$this->api->current_branch->id);

		}

	}
}