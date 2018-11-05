<?php

class page_agents extends Page{
	public $title ="Agent Management";

	function init(){
		parent::init();

		$this->add('Controller_Acl');

		$crud = $this->add('CRUD');
		$agent=$this->add('Model_Agent');
		$agent->setOrder('id','desc');

		if($crud->isEditing('edit')){
			$agent->hook('editing');
		}
		$agent->addExpression('sponsor_name')->set(function($m,$q){
			return $m->refSQL('sponsor_id')->fieldQuery('member');
		});

		$agent->addExpression('sponsor_cadre')->set(function($m,$q){
			return $m->refSQL('sponsor_id')->fieldQuery('cadre');	
		});

		$agent->addHook('beforeSave',function($m){
			if(!$m->loaded() && ($c = $m->ref('cadre_id')->get('name')) !='Advisor'){
				throw new \Exception("Please add only as Advisor, Adding (".$c.")", 1);
			}

			if($m->loaded() && $m->isDirty('cadre_id')){
				$old_agent = $this->add('Model_Agent')->load($m->id);
				$old_cader = $this->add('Model_Cadre')->load($old_agent['cadre_id']);
				$new_cader = $this->add('Model_Cadre')->load($m['cadre_id']);
				
				if($old_cader['total_crpb'] > $new_cader['total_crpb']){
					if($m->ref('Agent')->count()->getOne())
						throw new \Exception("Contains Agents and not allowed to go downgrade", 1);
						
				}

			}
			
		});

		// $agent->addCondition('sponsor_id','>',0);
		$arr1 = array('mo_id','member_id','sponsor_id','account_id','cadre_id','ActiveStatus','username','added_by','code_no');
		$arr2 = array('mo','code','created_at','member','sponsor_name','sponsor_cadre','account','cadre','current_individual_crpb','ActiveStatus','username','code_no','added_by');
		if($this->app->auth->model->isSuper()){
			$arr1[] = 'password';
			$arr2[] = 'password';
		}

		$crud->setModel($agent,$arr1,$arr2);

		if($crud and !$crud->isEditing('add') and ! $crud->isEditing('edit')){
			$crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentDocuments'));
			
			$agent_guarantor_crud = $crud->addRef('AgentGuarantor');
			if($agent_guarantor_crud and !$agent_guarantor_crud->isEditing('add') and !$agent_guarantor_crud->isEditing('edit')){
				$agent_guarantor_crud->add('Controller_DocumentsManager',array('doc_type'=>'AgentGuarantor'));
				$agent_guarantor_crud->add('Controller_Acl');
			}

			$crud->grid->addHook('formatRow',function($g){
				if(!$g->model['ActiveStatus']){
					$g->setTDParam('member','style/color','red');
					$g->setTDParam('member','style/text-decoration','line-through');
				}
				else
					$g->setTDParam('member','style/color','');
			});

			$crud->grid->addPaginator(50);
			$crud->grid->addQuickSearch(array('member','account','code'));
			$crud->grid->addFormatter('sponsor_name','Wrap');
		}


		if($crud->isEditing('add')){			//TODO 
			
			$account_of_member_field = $crud->form->getElement('account_id');
			
			$account_of_member_field->send_other_fields = array($crud->form->getElement('member_id'));
			if($member_selected = $_GET['o_'.$crud->form->getElement('member_id')->name]){
				$account_of_member_field->model->addCondition('member_id',$member_selected);
				$account_of_member_field->model->addCondition('ActiveStatus',true);
			}

		}

		if($crud->isEditing('edit')){			//TODO 
			
			$account_of_member_field = $crud->form->getElement('account_id');
			$account_of_member_field->model->addCondition('member_id',$crud->form->model['member_id']);
			// $account_of_member_field->model->addCondition('ActiveStatus',true);
		}

		$crud->add('Controller_Acl');
	}
}