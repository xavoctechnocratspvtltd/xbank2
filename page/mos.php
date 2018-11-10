<?php

class page_mos extends Page {
	public $title='Marketing manager (MO/RO) Management';

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$mo_tab = $tabs->addTab('Mo/RO List');
		$mo_change_tab = $tabs->addTab('Mo Agent Update');
		$association_edit = $tabs->addTabURL('romoassociationedit','MO/RO Association Edit');

		// MO/RO add edit
		$crud = $mo_tab->add('CRUD',['entity_name'=>'Mo/RO']);
		$crud->setModel('Mo');


		$branch_form = $mo_change_tab->add('Form');
		$branch_form->addField('DropDown','branch')->setModel('Branch');
		$branch_form->addSubmit('Filter');

		$agents = $mo_change_tab->add('Model_Agent');

		if($br = $this->app->stickyGet('branch')){
			$agents->addCondition('branch_id',$br);
		}else{
			$agents->addCondition('branch_id',-1);
		}

		$agent_grid = $mo_change_tab->add('Grid');
		$agent_grid->setModel($agents,['name','mo']);

		$agent_change_form = $mo_change_tab->add('Form');
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
				$ag_m['mo_id'] = $agent_change_form['to_mo'];
				$ag_m->save();
			}
			$mo_change_tab->js()->reload(['branch'=>$br])->execute();
		}
	}
}