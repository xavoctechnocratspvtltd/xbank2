<?php

class Model_Cadre extends Model_Table {
	public $table = "cadres";

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('percentage_share')->type('int');
		$this->addField('total_crpb')->type('int');
		$this->addField('req_under');
		// $this->addField('nextcadre_id')->type('int');
		$this->hasOne('NextCadre','nextcadre_id');

		$this->hasMany('Agent','cadre_id');
		$this->hasMany('PrevCadre','cadre_id');

		$this->setOrder('total_crpb asc');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function getNextCadre(){
		return $this->ref('nextcadre_id');
	}

	function getPrevCadre(){
		$t=$this->add('Model_Cadre')->tryLoadBy('nextcadre_id',$this->id);
		if($t->loaded()) return $t;

		return false;
	}

	function selfEfectivePercentage(){

		$per = $this['percentage_share'];
		$acc = $this;
		while($pc = $acc->getPrevCadre()){
			$per += $pc['percentage_share'];
			$acc= $pc;
		}

		return $per;

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
			$to_down_cadre = $to_down_cadre->getNextCadre();
		}
		
		if(!$found) return 0; // b y coming up I am not found .. may be you are trying below cadre

		return $percentage;
	}

	function loadLowestCader(){
		$this->loadBy('name','Advisor');
	}

}

class Model_NextCadre extends Model_Cadre{}
class Model_PrevCadre extends Model_Cadre{}