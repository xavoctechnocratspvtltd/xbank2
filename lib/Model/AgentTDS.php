<?php

class Model_AgentTDS extends Model_Table {
	
	public $table='agent_tds';

	function init(){
		parent::init();

		$this->hasOne('Agent','agent_id')->display(['form'=>'autocomplete/Basic']);
		// $this->hasOne('Transaction','transaction_id');
		$this->hasOne('Account','related_account_id')->display(['form'=>'autocomplete/Basic']);

		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);

		$this->addField('total_commission')->type('money');
		$this->addField('tds')->type('money');
		$this->addField('net_commission')->type('money');

		$this->addExpression('branch_id')->set($this->refSQL('related_account_id')->fieldQuery('branch_id'));
		$this->addExpression('branch')->set($this->refSQL('related_account_id')->fieldQuery('branch'));
		
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	// $this->add('Model_AgentTDS')->createNewEntry($agent_id,$transaction_id$related_account_id,$total_commission,$tds,$net_commission);
	function createNewEntry($agent_id,$transaction_id,$related_account_id,$total_commission,$tds,$net_commission){
		if($this->loaded()) throw new \Exception("Record already loaded, cannot create new", 1);

		if($total_commission == 0) return;
		
		$this['agent_id']=$agent_id;
		// $this['transaction_id']=$transaction_id; // not used now, we are dependent on related_account_id now
		$this['related_account_id']=$related_account_id;
		$this['total_commission']=$total_commission;
		$this['tds']=$tds;
		$this['net_commission']=$net_commission;

		$this->save();

		return $this;
	}


	function revertTdsEntries($on_date=null){

		if(!$on_date) $on_date = $this->app->now;

		$agent = $this->add('Model_AgentTDS');
		$agent->addExpression('yearly_commission')->set(function($m,$q){
			$fy = $this->app->getFinancialYear();
			$agtds = $this->add('Model_AgentTDS',['table_alias'=>'yc']);
			$agtds->addCondition('created_at','>=',$fy['start_date']);
			$agtds->addCondition('created_at','<',$this->app->nextDate($fy['end_date']));
			$agtds->addCondition('agent_id',$m->getElement('agent_id'));
			$agtds->addCondition('branch_id',$m->getElement('branch_id'));
			return $agtds->sum('total_commission');
		});

		$agent->addExpression('yearly_tds')->set(function($m,$q){
			$fy = $this->app->getFinancialYear();
			$agtds = $this->add('Model_AgentTDS',['table_alias'=>'ytds']);
			$agtds->addCondition('created_at','>=',$fy['start_date']);
			$agtds->addCondition('created_at','<',$this->app->nextDate($fy['end_date']));
			$agtds->addCondition('agent_id',$m->getElement('agent_id'));
			$agtds->addCondition('branch_id',$m->getElement('branch_id'));
			return $agtds->sum('tds');
		});


		$agent->addCondition('yearly_commission','<',TDS_ON_COMMISSION);
		$agent->_dsql()->group('agent_id,branch_id');

		// $grid = $this->add('Grid');
		// $grid->setModel($agent,['agent','yearly_commission','yearly_tds','account','branch_id','branch']);
		// $grid->addPaginator(100);

		$branch_array=[];
		foreach ($this->add('Model_Branch') as $br) {
			$branch_array[$br->id]=$this->add('Model_Branch')->load($br->id);
		}

		try{
			$this->api->db->dsql()->owner->beginTransaction();
			foreach ($agent as $ag) {
				$transaction = $this->add('Model_Transaction');
		        $transaction->createNewTransaction(TRA_TDS_REVERT, $branch_array[$ag['branch_id']], $on_date, "TDS Revert entry for ".$ag['agent'], $only_transaction=null, array('reference_id'=>null));
		        
		        $transaction->addDebitAccount($branch_array[$ag['branch_id']]['Code'] . SP . BRANCH_TDS_ACCOUNT, $ag['yearly_tds']);

		        $agent_saving_account = $ag->ref('agent_id')->ref('account_id');
				
		        $transaction->addCreditAccount($agent_saving_account, $ag['yearly_tds']);
		        
		        $transaction_id = $transaction->execute();
			}
			$this->api->db->dsql()->owner->commit();
		}catch(Exception $e){
			$this->api->db->dsql()->owner->rollBack();
			throw $e;
		}
	}
}