<?php


class page_reports_agent_tdsnewquarterly extends Page {
	public $title='Agent TDS Report New Quarterly';


	function init(){
		parent::init();

		$till_date = $this->api->today;
		$from_date = '01-01-1970';
		if($this->app->stickyGET('to_date')) $till_date = $_GET['to_date'];
		if($this->app->stickyGET('from_date')) $from_date = $_GET['from_date'];

		$form = $this->add('Form');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('Go');

		$agent = $this->add('Model_Agent');
		$agent->addExpression('to_date_commission')->set(function($m,$q)use($till_date){
			$fy = $this->app->getFinancialYear($till_date);
			$agtds = $this->add('Model_AgentTDS',['table_alias'=>'yc']);
			$agtds->addCondition('created_at','>=',$fy['start_date']);
			$agtds->addCondition('created_at','<',$this->app->nextDate($till_date));
			$agtds->addCondition('agent_id',$m->getElement('id'));
			// $agtds->addCondition('branch_id',$m->getElement('branch_id'));
			return $agtds->sum('total_commission');
		});

		$column_array=['agent_member_name','to_date_commission'];
		$condition_columns=[];


		if($_GET['filter']){
			$this->api->stickyGET("filter");
			$from_date = $this->api->stickyGET("from_date");
			$to_date = $this->api->stickyGET("to_date");
			$this->api->stickyGET("agent");

			$date_range = $this->app->get_date_ranges($from_date,$to_date);

			foreach ($date_range as $dr) {
				$start= $this->app->normalizeName($dr['start']);
				$end= $this->app->normalizeName($dr['end']);

				$agent->addExpression($start.$end.'commission')->set(function($m,$q)use($dr){
					$fy = $this->app->getFinancialYear();
					$agtds = $this->add('Model_AgentTDS',['table_alias'=>'yc']);
					$agtds->addCondition('created_at','>=',$dr['start']);
					$agtds->addCondition('created_at','<',$this->app->nextDate($dr['end']));
					$agtds->addCondition('agent_id',$m->getElement('id'));
					// $agtds->addCondition('branch_id',$m->getElement('branch_id'));
					return $agtds->sum('total_commission');
				})->caption('Commission<br/>'.$start.'</br>'.$end)
				->type('money')
				;

				$agent->addExpression($start.$end.'tds')->set(function($m,$q)use($dr){
					$fy = $this->app->getFinancialYear();
					$agtds = $this->add('Model_AgentTDS',['table_alias'=>'ytds']);
					$agtds->addCondition('created_at','>=',$dr['start']);
					$agtds->addCondition('created_at','<',$this->app->nextDate($dr['end']));
					$agtds->addCondition('agent_id',$m->getElement('id'));
					// $agtds->addCondition('branch_id',$m->getElement('branch_id'));
					return $agtds->sum('tds');
				})->caption('Tds<br/>'.$start.'</br>'.$end)
				->type('money')
				;

				$agent->addExpression($start.$end.'netcommission')->set(function($m,$q)use($dr){
					$fy = $this->app->getFinancialYear();
					$agtds = $this->add('Model_AgentTDS',['table_alias'=>'yc']);
					$agtds->addCondition('created_at','>=',$dr['start']);
					$agtds->addCondition('created_at','<',$this->app->nextDate($dr['end']));
					$agtds->addCondition('agent_id',$m->getElement('id'));
					// $agtds->addCondition('branch_id',$m->getElement('branch_id'));
					return $agtds->sum('net_commission');
				})->caption('Net Commission<br/>'.$start.'</br>'.$end)
				->type('money')
				;

				$condition_columns[] = $column_array[]=$start.$end.'commission';
				$condition_columns[] = $column_array[]=$start.$end.'tds';
				$condition_columns[] = $column_array[]=$start.$end.'netcommission';
			}

			if($_GET['agent']){
				$agent->addCondition('id',$_GET['agent']);
			}

			$or_cond = [];
			foreach ($condition_columns as $gc) {
				$or_cond[] = [$gc ,' > ',0];
			}
			if($or_cond)
				$agent->addCondition($or_cond);
		}
		
		$agent->addCondition('to_date_commission','>=',TDS_ON_COMMISSION);

		$grid = $this->add('Grid');
		$grid->setModel($agent,$column_array);
		$grid->addPaginator(200);

		if($form->isSubmitted()){
			$grid->js()->reload(array(
					'filter'=>1,
					'from_date'=>$form['from_date']?:0,
					'agent'=>$form['agent']?:0,
					'to_date'=>$form['to_date']?:0,
				))->execute();
		}
	}

}