<?php
class Model_Agent extends Model_Table {
	var $table= "agents";

	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','sponsor_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account_SavingAndCurrent','account_id')->caption('Saving Account')->display(array('form'=>'autocomplete/Basic'));;
		$this->hasOne('Cadre','cadre_id');
		// $this->hasOne('Tree','tree_id');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('AgentCode')->system(true);
		$this->addField('Path')->system(true);
		$this->addField('LegCount')->type('int')->system(true);
		$this->addField('Rank')->type('int')->system(true);
		$this->addField('BusinessCreditPoints')->type('int')->system(true);
		$this->addField('CumulativeBusinessCreditPoints')->type('int')->system(true);
		$this->addField('current_individual_crpb')->type('int')->caption('CRPB');
		// $this->addField('Rank_1_Count')->type('int');
		// $this->addField('Rank_2_Count')->type('int');
		// $this->addField('Rank_3_Count')->type('int');
		$this->hasMany('AgentGuarantor','agent_id');
		$this->hasMany('DocumentSubmitted','agent_id');
		
		$this->addExpression('code')->set($this->dsql()->concat($this->refSQL('account_id')->fieldQuery('branch_code'), ' ' , $this->getElement('id') ));

		$this->addExpression('agent_member_name')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('name');
		});

		$this->addExpression('agent_member_name_full')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('member_name');
		});

		$this->addExpression('agent_pan_no')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('PanNo');
		});
		$this->addExpression('name')->set($this->dsql()->concat(
				$this->getElement('code'),' ', $this->getElement('agent_member_name_full')
			));


		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeSave',$this);

		$this->addHook('editing',array($this,'defaultEditing'));

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function defaultEditing(){
		$this->getElement('member_id')->system(true);
	}

	function beforeDelete(){
		throw new Exception("Agent Delete Hook ????", 1);
		
	}

	function beforeSave(){
		if($this->sponsor() and $this->sponsor()->isAtLowestCader()){
			throw $this->exception('Sponsor is Advisor . Cannot Add','ValidityCheck')->setField('sponsor_id');
		}

		//Member ke is_agent field true 
		$member  = $this->ref('member_id');
		$member['is_agent'] = true;
		$member->save();
	}

	function account(){
		return $this->ref('account_id');
	}

	function sponsor(){
		if($this->ref('sponsor_id')->loaded()) return $this->ref('sponsor_id');
		return false;
	}

	function cadre(){
		return $this->ref('cadre_id');
	}

	function addCRPB($crpb, $amount){
		$this['current_individual_crpb'] =  $this['current_individual_crpb'] + ($crpb  * $amount  / 100.00);
		$this->save();
	}

	function isAtLowestCader(){
		return ($this->cadre()->get('name') == 'Advisor');
	}

	function isHighestCadre(){
		return $this->cadre()->getNextCadre()->get('id') == '';
	}

	// function beforeDelete(){
	// 	$agent=$this->add('Model_Agent');

	// 	$agent_member_j=$agent->join('members','member_id');
	// 	$agent_member_j->hasMany('Account')
		
	// }
}