<?php

class Model_DSAGuarantor extends Model_Table {
	public $table ="dsa_guarantors";

	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('DSA','dsa_id');

		$this->hasMany('DocumentSubmitted','dsaguarantor_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

} 