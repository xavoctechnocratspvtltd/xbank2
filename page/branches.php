<?php

class page_branches extends Page {

	public $title = 'Branch Management';
		
	function init(){
		parent::init();

		try{
			$this->api->db->dsql()->owner->beginTransaction();

			$branch_crud = $this->add('CRUD');
			
			$branch_model = $this->add('Model_Branch');
			$branch_model->addExpression('last_closing')->set($branch_model->refSQL('Closing')->setLimit(1)->fieldQuery('daily'));
			
			if($branch_crud->isEditing('edit')){
				$branch_model->getElement('Code')->system(true);
			}

			$branch_crud->setModel($branch_model);

			$this->api->db->dsql()->owner->commit();
		}catch(Exception $e){
			$this->api->db->dsql()->owner->rollBack();
			throw $e;
		}

	}
}