<?php

class Model_Cadre extends SQL_Model {
	public $table = "cadres";

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('percentage_share')->type('int');
		$this->addField('total_crpb');
		$this->addField('req_under');
		// $this->addField('nextcadre_id')->type('int');
		$this->hasOne('NextCadre','nextcadre_id');

		$this->hasMany('Agent','cadre_id');
		$this->hasMany('PrevCadre','cadre_id');

		$this->setOrder(array('percentage_share desc','total_crpb desc'));

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}

class Model_NextCadre extends Model_Cadre{}
class Model_PrevCadre extends Model_Cadre{}