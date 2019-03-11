<?php

class page_moagentupdate extends Page {
	public $title="MO Agent Update";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);

		$branch_form = $this->add('Form');
		$branch_form->addField('DropDown','branch')->setModel('Branch');
		$branch_form->addSubmit('Filter');

		$agents = $this->add('Model_Agent');

		if($br = $this->app->stickyGet('branch')){
			$agents->addCondition('branch_id',$br);
		}else{
			$agents->addCondition('branch_id',-1);
		}

		$agent_grid = $this->add('Grid');
		$agent_grid->setModel($agents,['name','mo']);

		$agent_change_form = $this->add('Form');
		$agent_change_form->addField('DropDown','to_mo')->setEmptyText("Please Select")->setModel('Mo');
		$agent_change_form->addField('Checkbox','remove_mo');
		$agent_list_field = $agent_change_form->addField('Hidden','agent_list');
		$agent_change_form->addSubmit('Change Mo');

		// selectable field
		$agent_grid->addSelectable($agent_list_field);

		if($branch_form->isSubmitted()){
			$agent_grid->js()->reload(['branch'=>$branch_form['branch']?:0])->execute();
		}

		if($agent_change_form->isSubmitted()){
			if(!$agent_change_form['remove_mo'] && !$agent_change_form['to_mo']){
				$agent_change_form->displayError('to_mo','Select Mo or check remove_mo');
			}

			if($agent_change_form['remove_mo'] && $agent_change_form['to_mo']){
				$agent_change_form->displayError('to_mo','Do not Select Mo if checked remove_mo');
			}
			
			$agent_list = json_decode($agent_change_form['agent_list'],true);

			if(count($agent_list) == 0 ){
				throw new \Exception("No Agent Selected", 1);
			}

			foreach ($agent_list as $ag) {
				$ag_m = $this->add('Model_Agent');
				$ag_m->load($ag);
				
				if($agent_change_form['remove_mo'])
					$ag_m['mo_id'] = null;
				else
					$ag_m['mo_id'] = $agent_change_form['to_mo'];
				$ag_m->save();
			}
			$this->js()->reload(['branch'=>$br])->execute();
		}
	}
}