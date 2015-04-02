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

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function getNextCadre(){
		return $this->ref('nextcadre_id');
	}

	function cumulativePercantage($to_down_cadre){
		$percentage = 0;
		$found = false;
		while($to_down_cadre['nextcadre_id']){
			$percentage += $to_down_cadre->getNextCadre()->get('percentage_share');
			if($to_down_cadre['nextcadre_id'] == $this->id ) {
				$found = true;
				break;
			}
		}
		
		if(!$found) return 0; // b y coming up I am not found .. may be you are trying below cadre

		return $percentage;
	}

}

class Model_NextCadre extends Model_Cadre{}
class Model_PrevCadre extends Model_Cadre{}