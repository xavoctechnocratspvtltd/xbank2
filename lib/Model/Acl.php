<?php

class Model_Acl extends Model_Table {
	public $table ='acls';
	public $class=null;

	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id');

		$this->hasOne('DocumentAll','documents_id');

		$this->addField('class');

		$this->addField('can_view')->type('boolean')->defaultValue(true);
		$this->addField('is_all_branch_allowed')->type('boolean')->defaultValue(false);
		$this->addField('allow_add')->type('boolean')->defaultValue(false);
		$this->addField('allow_edit')->type('boolean')->defaultValue(false);
		$this->addField('allow_del')->type('boolean')->defaultValue(false)->caption('Allow Delete');


		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function setModelClass($class){
		$this->class = $class;
		$this->addCondition('class',$this->class);
		return $this;
	}

	function setStaff($staff){
		$this->addCondition('staff_id',$staff->id);
		return $this;
	}

	function canView(){
		if(!$this->loaded()) $this->tryLoadAny();
		return $this['can_view'];
	}

	function isCurrentBranchOnly(){
		if(!$this->loaded()) $this->tryLoadAny();
		return !$this['is_all_branch_allowed'];
	}

	function allowAdd(){
		if(!$this->loaded()) $this->tryLoadAny();
		return $this['allow_add'];
	}

	function allowEdit(){
		if(!$this->loaded()) $this->tryLoadAny();
		return $this['allow_edit'];
	}

	function allowDelete(){
		if(!$this->loaded()) $this->tryLoadAny();
		return $this['allow_del'];
	}

	function isDocument(){
		if(!$this->loaded()) $this->tryLoadAny();
		
		return ($this['class'] == "Model_DocumentSubmitted");
	}

}