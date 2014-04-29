<?php

class Model_Member extends Model_Table {
	var $table= "members";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id');
		$this->addField('name')->mandatory(true);
		$this->addField('CurrentAddress');
		$this->addField('FatherName');
		$this->addField('Cast');
		$this->addField('PermanentAddress');
		$this->addField('Occupation');
		$this->addField('Age');
		$this->addField('Nominee');
		$this->addField('RelationWithNominee');
		$this->addField('NomineeAge');
		$this->addField('Witness1Name');
		$this->addField('Witness1FatherName');
		$this->addField('Witness1Address');
		$this->addField('Witness2Name');
		$this->addField('Witness2FatherName');
		$this->addField('Witness2Address');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('PhoneNos');
		$this->addField('PanNo');
		$this->addField('IsMinor');
		$this->addField('MinorDOB');
		$this->addField('ParentName');
		$this->addField('RelationWithParent');
		$this->addField('ParentAddress');
		$this->addField('DOB')->type('date');
		$this->addField('FilledForm60')->type('boolean')->mandatory(true);
		$this->addField('IsCustomer')->type('boolean')->mandatory(true);
		$this->addField('IsMember')->type('boolean')->mandatory(true);

		$this->hasMany('Jointmember','member_id');

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

}