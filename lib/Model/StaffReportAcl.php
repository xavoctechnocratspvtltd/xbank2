<?php


class Model_StaffReportAcl extends Model_Table {
	public $table = "staff_report_acl";

	function init(){
		parent::init();

		$this->hasOne('Staff','staff_id');
		$this->addField('page');
		$this->addField('is_allowed')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}