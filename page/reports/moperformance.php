<?php

class page_reports_moperformance extends Page {
	public $title="MO Performance";

	function init(){
		parent::init();
		
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$mo_id = $this->app->stickyGET('mo_id');
		$filter = $this->app->stickyGET('filter');

		$vp = $this->add('VirtualPage');
		$vp->set([$this,'displayAgentAccoutns']);

		$form = $this->add('Form');
		$field_mo = $form->addField('autocomplete/Basic','mo');
		$field_mo->setModel('Mo');
		$field_mo->validateNotNull();

		$form->addField('DatePicker','from_date')->validateNotNull();;
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addSubmit('Filter');

		$model = $this->add('Model_MoAgentAssociation');

		if($filter){

			$model->addExpression('effective_from')->set(function($m,$q)use($from_date){
				return $q->expr('GREATEST([0],"[1]")',[$m->getElement('from_date'),$from_date]);
			})->type('datetime');

			$model->addExpression('effective_to')->set(function($m,$q)use($to_date){
				return $q->expr('LEAST([0],"[1]")',[$m->getElement('to_date'),$this->app->nextDate($to_date)]);
			});

			$model->addExpression('crpb_sum')->set(function($m,$q)use($from_date, $to_date){
				$a = $m->add('Model_Account');
				$a
					->addCondition('agent_id',$m->getElement('agent_id'))
					->addCondition($q->expr('[0] >= GREATEST([1],"[2]")',[$a->getElement('created_at'),$m->getElement('from_date'),$from_date]))
					->addCondition($q->expr('[0] <= LEAST([1],"[2]")',[$a->getElement('created_at'),$m->getElement('to_date'),$to_date]))
					;
				return $a->sum('crpb');
			})->type('money');

			$model->addExpression('to_date_date')->set(function($m,$q){
				return $q->expr('Date([0])',[$m->getElement('to_date')]);
			});
			
			$model->addCondition('mo_id',$mo_id);
			$model->addCondition('from_date','<=',$to_date);
			$model->addCondition('to_date_date','>=',$from_date);

		}else{
			$model->addCondition('id','-1');
		}

		$grid = $this->add('Grid');
		$grid->setModel($model);
		//$grid->removeColumn('from_date');
		//$grid->removeColumn('to_date');
		$grid->removeColumn('_to_date');
		$grid->removeColumn('to_date_date');
		$grid->addFormatter('agent','WRAP');

		if($filter){
			$grid->setFormatter('crpb_sum','template')->setTemplate('<a class="crbp" href="#" data-agentid="{$agent_id}" data-agentfromdate="{$effective_from}" data-agenttodate="{$effective_to}" >{$crpb_sum}</a>','crpb_sum');
			$grid->js('click')->_selector('.crbp')->univ()->frameURL([
											$vp->getUrl(),
											'agent_id'=>$this->js()->_selectorThis()->data('agentid'),
											'agent_from_date'=>$this->js()->_selectorThis()->data('agentfromdate'),
											'agent_to_date'=>$this->js()->_selectorThis()->data('agenttodate'),
										]);
		}

		if($form->isSubmitted()){

			if(strtotime($form['from_date']) > strtotime($form['to_date']) ){
				$form->displayError('from_date','From Date must be smaller than To date');
			}

			$grid->js()->reload(array(
								'mo_id'=>$form['mo'],
								'from_date'=>$form['from_date'],
								'to_date'=>$form['to_date'],
								'filter'=>1
							))->execute();
		}

	}

	function displayAgentAccoutns($page){
		$this->app->stickyGET('from_date');
		$this->app->stickyGET('to_date');
		$this->app->stickyGET('agent_id');

		$model = $this->add('Model_Account');
		$model->addCondition('created_at','>=',$_GET['from_date']);
		$model->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));
		$model->addCondition('agent_id',$_GET['agent_id']);

		$grid = $page->add('Grid');
		$grid->setModel($model,['AccountNumber','scheme','member','Amount','crpb','created_at']);
		$grid->addFormatter('member','WRAP');
		$grid->addPaginator(50);
		$grid->addTotals(['crpb']);
	}
}