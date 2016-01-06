<?php
class page_utility_crpbedit extends Page{
	function init(){
		parent::init();
		$agent=$this->add('Model_Agent');

		// $vp = $this->add('VirtualPage')->set(function($p){
		// 	$vp_model=$p->add('Model_Agent');

		// 	$staff=$p->add('Model_Staff')->load($this->api->auth->model->id);
		// 	$pass=$staff->get('password');

		// 	// echo "string".$pass;
		// 	$form=$p->add('Form');
		// 		$form->addField('password','password');
		// 		$form->addSubmit('submit');


		// 	if($form->isSubmitted()){
		// 		if($form['password']!=$pass)
		// 			$form->error('password','password not match');
		// 		$agent_model=$this->add('Model_Agent');
		// 		$this->api->db->dsql()->expr("UPDATE `agents` SET `current_individual_crpb_old` =`current_individual_crpb` ")->execute();
		// 		$this->api->db->dsql()->expr("UPDATE `agents` SET `current_individual_crpb` = 0")->execute();
		// 		$form->js()->univ()->location()->execute();				
		// 	}
		// });

		$crud = $this->add('CRUD',array('allow_add'=>false,'allow_del'=>false));

		// $btn=$crud->grid->addButton('Clear');
		
		// $btn->onClick(function($btn)use($vp){
		// 	return $btn->js()->univ()->frameURL('Clear CRPB',$this->api->url($vp->getURL()));
		// });

		$crud->setmodel($agent,array('code','name','current_individual_crpb','updated_at'));
		if(!$crud->isEditing()){
			$g=  $crud->grid;

			$g->addPaginator(100);

			$g->addFormatter('current_individual_crpb','grid/inline');

			$g->addQuickSearch(array('name'));
			
		}

	}
}