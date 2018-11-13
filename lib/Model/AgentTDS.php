<?php

class Model_AgentTDS extends Model_Table {
	
	public $table='agent_tds';

	function init(){
		parent::init();

		$this->hasOne('Agent','agent_id');
		$this->hasOne('Transaction','transaction_id');
		$this->hasOne('Account','related_account_id');

		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);

		$this->addField('total_commission')->type('money');
		$this->addField('tds')->type('money');
		$this->addField('net_commission')->type('money');
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	// $this->add('Model_AgentTDS')->createNewEntry($agent_id,$transaction_id$related_account_id,$total_commission,$tds,$net_commission);
	function createNewEntry($agent_id,$transaction_id,$related_account_id,$total_commission,$tds,$net_commission){
		if($this->loaded()) throw new \Exception("Record already loaded, cannot create new", 1);

		if($total_commission == 0) return;
		
		$this['agent_id']=$agent_id;
		$this['transaction_id']=$transaction_id;
		$this['related_account_id']=$related_account_id;
		$this['total_commission']=$total_commission;
		$this['tds']=$tds;
		$this['net_commission']=$net_commission;

		$this->save();

		return $this;
	}
}