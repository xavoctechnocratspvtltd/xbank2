<?php
class Model_Agent extends Model_Table {
	var $table= "agents";

	public $from_date=null;
	public $to_date = null;

	function init(){
		parent::init();

		$this->hasOne('Mo','mo_id')->display(array('form'=>'autocomplete/Basic'))->sortable(true);
		$this->hasOne('Member','member_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('ParentAgent','sponsor_id')->display(array('form'=>'autocomplete/Basic'))->sortable(true);
		$this->hasOne('Account_SavingAndCurrent','account_id')->caption('Saving Account')->display(array('form'=>'autocomplete/Basic'))->mandatory(true);
		$this->hasOne('Cadre','cadre_id')->mandatory(true);
		// $this->hasOne('Tree','tree_id');
		$this->addField('added_by');

		$this->addField('username')->mandatory(true);
		$this->addField('password')->mandatory(true);
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->system(true);
		$this->addField('AgentCode')->system(true);
		$this->addField('Path')->system(true);
		$this->addField('LegCount')->type('int')->system(true);
		$this->addField('Rank')->type('int')->system(true);
		$this->addField('BusinessCreditPoints')->type('int')->system(true);
		$this->addField('CumulativeBusinessCreditPoints')->type('int')->system(true)->defaultValue(0);
		$this->addField('current_individual_crpb')->type('int')->caption('CRPB')->defaultValue(0);
		$this->addField('current_individual_crpb_old')->type('int')->defaultValue(0)->system(true);
		// $this->addField('Rank_1_Count')->type('int');
		// $this->addField('Rank_2_Count')->type('int');
		// $this->addField('Rank_3_Count')->type('int');
		$this->hasMany('AgentGuarantor','agent_id');
		$this->hasMany('DocumentSubmitted','agent_id');
		$this->hasMany('Agent','sponsor_id');
		$this->hasMany('AgentTDS','agent_id');
		
		$this->addField('code_no');
		$this->addExpression('code')->set('CONCAT("BCCSAG ",IFNULL(code_no,""))');

		$this->addExpression('sponsor_cadre')->set(function($m,$q){
			return $m->refSQL('sponsor_id')->setLimit(1)->fieldQuery('cadre');
		});

		$this->addExpression('AccountNumber')->set(function($m,$q){
			return $m->refSQL('account_id')->fieldQuery('AccountNumber');
		});
		$this->addExpression('branch_id')->set(function($m,$q){
			return $m->refSQL('account_id')->fieldQuery('branch_id');
		});

		$this->addExpression('agent_member_name')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('name');
		});

		$this->addExpression('agent_member_name_full')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('member_name');
		});

		$this->addExpression('agent_pan_no')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('PanNo');
		});
		$this->addExpression('agent_member_address')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('CurrentAddress');
		});
		$this->addExpression('agent_member_father_name')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('FatherName');
		});

		$this->addExpression('agent_phone_no')->set(function($m,$q){
			return $m->refSQL('member_id')->fieldQuery('PhoneNos');
		});
		
		$this->addExpression('name')->set($this->dsql()->concat(
				$this->getElement('code'),' ', $this->getElement('agent_member_name_full')
		));
		$self=$this;
		$this->addExpression('self_crpb')->set(function($m,$q)use($self){
			$acc = $m->add('Model_Account',array('self_crpb_account'));
			$acc->addCondition('agent_id',$q->getField('id'));

			if(!$m->from_date){
				if(date('m',strtotime($self->api->today)) >= 6){
				 	$m->from_date = date('Y',strtotime($self->api->today))."-06-01";
				}else{
				 	$m->from_date = date('Y',strtotime($self->api->today))."-01-01";
				}
			} 

			if(!$m->to_date){
				if(date('m',strtotime($self->api->today)) >= 6) 
					$m->to_date = date('Y',strtotime($self->api->today))."-12-31";
				else	
					$m->to_date = date('Y',strtotime($self->api->today))."-05-30";	
			}
			$acc->addCondition('created_at','>=',$m->from_date);
			$acc->addCondition('created_at','<',$m->api->nextDate($m->to_date));

			return $acc->sum('crpb');

		})->sortable(true);

		$this->addExpression('level_1_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));
			return $ls1->sum($q->expr('IFNULL([0],0)',array($ls1->getElement('self_crpb'))));
		});

		$this->addExpression('level_2_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);
			return $ls2->sum($q->expr('IFNULL([0],0)',array($ls2->getElement('self_crpb'))));
		});

		$this->addExpression('level_3_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			return $ls3->sum($q->expr('IFNULL([0],0)',array($ls3->getElement('self_crpb'))));
		});

		$this->addExpression('level_4_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			return $ls4->sum($q->expr('IFNULL([0],0)',array($ls4->getElement('self_crpb'))));
		});

		$this->addExpression('level_5_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			return $ls5->sum($q->expr('IFNULL([0],0)',array($ls5->getElement('self_crpb'))));
		});

		$this->addExpression('level_6_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			return $ls6->sum($q->expr('IFNULL([0],0)',array($ls6->getElement('self_crpb'))));
		});

		$this->addExpression('level_7_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			return $ls7->sum($q->expr('IFNULL([0],0)',array($ls7->getElement('self_crpb'))));
		});

		$this->addExpression('level_8_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			return $ls8->sum($q->expr('IFNULL([0],0)',array($ls8->getElement('self_crpb'))));
		});

		$this->addExpression('level_9_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			$ls9 = $m->add('Model_Agent',array('table_alias'=>'l9'));
			$ls9->addCondition($q->expr('[0] in ([1])',
				array(
					$ls9->getElement('sponsor_id'),
					$ls8->fieldQuery('id')
					)
				)
			);

			return $ls9->sum($q->expr('IFNULL([0],0)',array($ls9->getElement('self_crpb'))));
		});

		$this->addExpression('level_10_crpb')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			$ls9 = $m->add('Model_Agent',array('table_alias'=>'l9'));
			$ls9->addCondition($q->expr('[0] in ([1])',
				array(
					$ls9->getElement('sponsor_id'),
					$ls8->fieldQuery('id')
					)
				)
			);

			$ls10 = $m->add('Model_Agent',array('table_alias'=>'l10'));
			$ls10->addCondition($q->expr('[0] in ([1])',
				array(
					$ls10->getElement('sponsor_id'),
					$ls9->fieldQuery('id')
					)
				)
			);

			return $ls10->sum($q->expr('IFNULL([0],0)',array($ls10->getElement('self_crpb'))));
		});

		$this->addExpression('total_group_crpb')->set(
			$this->dsql()->expr('IFNULL([0],0) + IFNULL([1],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0)',
				array(
					$this->getElement('level_1_crpb'), 
					$this->getElement('level_2_crpb'),
					$this->getElement('level_3_crpb'),
					$this->getElement('level_4_crpb'),
					$this->getElement('level_5_crpb'),
					$this->getElement('level_6_crpb'),
					$this->getElement('level_7_crpb'),
					$this->getElement('level_8_crpb'),
					$this->getElement('level_9_crpb'),
					$this->getElement('level_10_crpb')
					)
				)
			);


		$this->addExpression('level_1_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));
			return $ls1->count();
		});

		$this->addExpression('level_2_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);
			return $ls2->count();
		});

		$this->addExpression('level_3_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			return $ls3->count();
		});

		$this->addExpression('level_4_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4c'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			return $ls4->count();
		});

		$this->addExpression('level_5_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4c'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5c'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			return $ls5->count();
		});

		$this->addExpression('level_6_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4c'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5c'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6c'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			return $ls6->count();
		});

		$this->addExpression('level_7_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4c'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5c'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6c'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7c'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			return $ls7->count();
		});

		$this->addExpression('level_8_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4c'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5c'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6c'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7c'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8c'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			return $ls8->count();
		});

		$this->addExpression('level_9_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4c'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5c'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6c'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7c'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8c'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			$ls9 = $m->add('Model_Agent',array('table_alias'=>'l9c'));
			$ls9->addCondition($q->expr('[0] in ([1])',
				array(
					$ls9->getElement('sponsor_id'),
					$ls8->fieldQuery('id')
					)
				)
			);

			return $ls9->count();
		});

		$this->addExpression('level_10_count')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1c'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2c'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3c'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4c'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5c'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6c'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7c'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8c'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			$ls9 = $m->add('Model_Agent',array('table_alias'=>'l9c'));
			$ls9->addCondition($q->expr('[0] in ([1])',
				array(
					$ls9->getElement('sponsor_id'),
					$ls8->fieldQuery('id')
					)
				)
			);

			$ls10 = $m->add('Model_Agent',array('table_alias'=>'l10c'));
			$ls10->addCondition($q->expr('[0] in ([1])',
				array(
					$ls10->getElement('sponsor_id'),
					$ls9->fieldQuery('id')
					)
				)
			);

			return $ls10->count();
		});

		$this->addExpression('total_group_count')->set(
			$this->dsql()->expr('IFNULL([0],0) + IFNULL([1],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0)',
				array(
					$this->getElement('level_1_count'), 
					$this->getElement('level_2_count'),
					$this->getElement('level_3_count'),
					$this->getElement('level_4_count'),
					$this->getElement('level_5_count'),
					$this->getElement('level_6_count'),
					$this->getElement('level_7_count'),
					$this->getElement('level_8_count'),
					$this->getElement('level_9_count'),
					$this->getElement('level_10_count')
					)
				)
			);


		$this->addExpression('self_business')->set(function($m,$q){
			$acc =  $m->add('Model_Account',array('table_alias'=>'total_self_business'))
						->addCondition('agent_id',$q->getField('id'))
						->addCondition('SchemeType',array('DDS','FixedAndMis','Recurring'));
			if($m->from_date)
				$acc->addCondition('created_at','>=',$m->from_date);
			if($m->to_date)
				$acc->addCondition('created_at','<',$m->api->nextDate($m->to_date));
			
			return $acc->sum('Amount');
		})->sortable(true);

		$this->addExpression('level_1_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));
			return $ls1->sum($q->expr('IFNULL([0],0)',array($ls1->getElement('self_business'))));
		});

		$this->addExpression('level_2_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);
			return $ls2->sum($q->expr('IFNULL([0],0)',array($ls2->getElement('self_business'))));
		});

		$this->addExpression('level_3_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			return $ls3->sum($q->expr('IFNULL([0],0)',array($ls3->getElement('self_business'))));
		});

		$this->addExpression('level_4_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4sb'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			return $ls4->sum($q->expr('IFNULL([0],0)',array($ls4->getElement('self_business'))));
		});

		$this->addExpression('level_5_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4sb'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5sb'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			return $ls5->sum($q->expr('IFNULL([0],0)',array($ls5->getElement('self_business'))));
		});

		$this->addExpression('level_6_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4sb'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5sb'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6sb'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			return $ls6->sum($q->expr('IFNULL([0],0)',array($ls6->getElement('self_business'))));
		});

		$this->addExpression('level_7_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4sb'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5sb'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6sb'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7sb'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			return $ls7->sum($q->expr('IFNULL([0],0)',array($ls7->getElement('self_business'))));
		});

		$this->addExpression('level_8_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4sb'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5sb'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6sb'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7sb'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8sb'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			return $ls8->sum($q->expr('IFNULL([0],0)',array($ls8->getElement('self_business'))));
		});

		$this->addExpression('level_9_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4sb'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5sb'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6sb'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7sb'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8sb'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			$ls9 = $m->add('Model_Agent',array('table_alias'=>'l9sb'));
			$ls9->addCondition($q->expr('[0] in ([1])',
				array(
					$ls9->getElement('sponsor_id'),
					$ls8->fieldQuery('id')
					)
				)
			);

			return $ls9->sum($q->expr('IFNULL([0],0)',array($ls9->getElement('self_business'))));
		});

		$this->addExpression('level_10_self_business')->set(function($m,$q){
			$ls1 = $m->add('Model_Agent',array('table_alias'=>'l1sb'));
			$ls1->addCondition($q->expr('[0] in ([1])',array($ls1->getElement('sponsor_id'),$m->getElement('id'))));

			$ls2 = $m->add('Model_Agent',array('table_alias'=>'l2sb'));
			$ls2->addCondition($q->expr('[0] in ([1])',
				array(
					$ls2->getElement('sponsor_id'),
					$ls1->fieldQuery('id')
					)
				)
			);

			$ls3 = $m->add('Model_Agent',array('table_alias'=>'l3sb'));
			$ls3->addCondition($q->expr('[0] in ([1])',
				array(
					$ls3->getElement('sponsor_id'),
					$ls2->fieldQuery('id')
					)
				)
			);

			$ls4 = $m->add('Model_Agent',array('table_alias'=>'l4sb'));
			$ls4->addCondition($q->expr('[0] in ([1])',
				array(
					$ls4->getElement('sponsor_id'),
					$ls3->fieldQuery('id')
					)
				)
			);

			$ls5 = $m->add('Model_Agent',array('table_alias'=>'l5sb'));
			$ls5->addCondition($q->expr('[0] in ([1])',
				array(
					$ls5->getElement('sponsor_id'),
					$ls4->fieldQuery('id')
					)
				)
			);

			$ls6 = $m->add('Model_Agent',array('table_alias'=>'l6sb'));
			$ls6->addCondition($q->expr('[0] in ([1])',
				array(
					$ls6->getElement('sponsor_id'),
					$ls5->fieldQuery('id')
					)
				)
			);

			$ls7 = $m->add('Model_Agent',array('table_alias'=>'l7sb'));
			$ls7->addCondition($q->expr('[0] in ([1])',
				array(
					$ls7->getElement('sponsor_id'),
					$ls6->fieldQuery('id')
					)
				)
			);

			$ls8 = $m->add('Model_Agent',array('table_alias'=>'l8sb'));
			$ls8->addCondition($q->expr('[0] in ([1])',
				array(
					$ls8->getElement('sponsor_id'),
					$ls7->fieldQuery('id')
					)
				)
			);

			$ls9 = $m->add('Model_Agent',array('table_alias'=>'l9sb'));
			$ls9->addCondition($q->expr('[0] in ([1])',
				array(
					$ls9->getElement('sponsor_id'),
					$ls8->fieldQuery('id')
					)
				)
			);

			$ls10 = $m->add('Model_Agent',array('table_alias'=>'l10sb'));
			$ls10->addCondition($q->expr('[0] in ([1])',
				array(
					$ls10->getElement('sponsor_id'),
					$ls9->fieldQuery('id')
					)
				)
			);

			return $ls10->sum($q->expr('IFNULL([0],0)',array($ls10->getElement('self_business'))));
		});


		$this->addExpression('total_team_business')->set(
			$this->dsql()->expr('IFNULL([0],0) + IFNULL([1],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0) + IFNULL([2],0)',
				array(
					$this->getElement('level_1_self_business'), 
					$this->getElement('level_2_self_business'),
					$this->getElement('level_3_self_business'),
					$this->getElement('level_4_self_business'),
					$this->getElement('level_5_self_business'),
					$this->getElement('level_6_self_business'),
					$this->getElement('level_7_self_business'),
					$this->getElement('level_8_self_business'),
					$this->getElement('level_9_self_business'),
					$this->getElement('level_10_self_business')
					)
				)
			);

		// $this->debug();

		// $this->addExpression('level_2_crpb')->set(function($m,$q){
		// 	return $d->expr("SELECT sum(current_individual_crpb) WHERE id in [0]",$m->refSQL('Agent'));
		// 	return $m->add('Model_Agent')->addCondition('sponsor_id',$q->getField('id'))->sum('current_individual_crpb');
		// });

		// $this->addExpression('total_group_crpb')->set()

		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeSave',$this);
		$this->addHook('beforeInsert',$this);

		$this->addHook('editing',array($this,'defaultEditing'));

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function defaultEditing(){
		$this->getElement('member_id')->system(true);
	}

	function beforeDelete(){
		if($this->add('Model_Account')->addCondition('agent_id',$this->id)->count()->getOne() > 0 )
			throw new Exception("Agent Has Accounts", 1);

		$member  = $this->ref('member_id');
		$member['is_agent'] = false;
		$member->save();
		
	}

	function beforeInsert(){
		$member  = $this->ref('member_id');
		if($member['is_agent']) throw $this->exception('This member is already agent','ValidityCheck')->setField('member_id');
		//Member ke is_agent field true 
		$member['is_agent'] = true;
		$member->save();
	}

	function beforeSave(){
		

		$old_agent_username = $this->add('Model_Agent');
		$old_agent_username->addCondition('username',$this['username']);
		$old_agent_username->addCondition('username','<>',null);

		if($this->loaded())
			$old_agent_username->addCondition('id','<>',$this['id']);

		$old_agent_username->tryLoadAny();

		if($old_agent_username->loaded())
			throw $this->exception('Username already taken','ValidityCheck')->setField('username');

		if($this->sponsor() and $this->sponsor()->isAtLowestCader()){
			throw $this->exception('Sponsor is Advisor . Cannot Add','ValidityCheck')->setField('sponsor_id');
		}

		$this['updated_at'] = $this->api->now;

		if(!$this->loaded()) $this['code_no']= $this->add('Model_Agent')->_dsql()->del('fields')->field('max(code_no)')->getOne() + 1;

		// update mo_agent history if mo_id is changed
		if($this->isDirty('mo_id')){

			$current_mo_id = $this['mo_id'];

			if($this->loaded()){
				$old_agent = $this->add('Model_Agent')->load($this->id);
				// update _to_date of last mo assocition
				if($current_mo_id != $old_agent['mo_id']){
					$asso = $this->add('Model_MoAgentAssociation');
					$asso->addCondition('agent_id',$this->id);
					$asso->addCondition('mo_id',$old_agent['mo_id']);
					$asso->addCondition('from_date','<>',null);
					$asso->addCondition('_to_date',null);
					$asso->tryLoadAny();
					if($asso->loaded()){
						$asso['_to_date'] = $this->app->now;
						$asso->saveAndUnload();
					}
				}
			}

			// new mo agent association entry
			if($current_mo_id){
				$new_asso = $this->add('Model_MoAgentAssociation');
				$new_asso['agent_id'] = $this->id;
				$new_asso['mo_id'] = $current_mo_id;
				$new_asso['from_date'] = $this->app->now;
				$new_asso->save();
			}
			
  		}

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
		$this['CumulativeBusinessCreditPoints'] =  $this['CumulativeBusinessCreditPoints'] + ($crpb  * $amount  / 100.00);
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