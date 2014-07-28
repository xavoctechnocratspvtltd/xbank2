<?php

class Model_DSAGuarantor extends Model_Table {
	public $table ="dsa_guarantors";

	function init(){
		parent::init();

		$this->hasOne('Member','member_id');

		$this->hasMany('DocumentSubmitted','dsaguarantor_id');
	}

} 