<?php

class page_reports_dealerdsa_dsa extends Page {
	
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('autocomplete/Basic','dsa')->setModel('DSA');
		$form->addSubmit('Details');

		$view = $this->add('View');

		$grid = $view->add('Grid');
		$m = $this->add('Model_Dealer');

		if($_GET['dsa']){
			$this->api->stickyGET('dsa');
			$dsa_m = $this->add('Model_DSA')->load($_GET['dsa']);
			$grid->add('H3',null,'quick_search')->set($dsa_m['name']);

			$view->add('H3')->set('Documents');

			$documents_grid = $view->add('Grid_AccountsBase');
			$documents_grid->setModel($dsa_m->ref('DocumentSubmitted'),array('documents','Description','doc_image'));
			$documents_grid->addFormatter('doc_image','picture');

			$view->add('H3')->set('Guarantors');
			$guar_grid = $view->add('Grid');
			
			$guar_m = $dsa_m->ref('DSAGuarantor');
			$mem_j = $guar_m->join('members','member_id');
			$mem_j->addField('PhoneNos');

			$guar_grid->setModel($dsa_m->ref('DSAGuarantor'));
			$guar_grid->removeColumn('dsa');

			$m->addCondition('dsa_id',$_GET['dsa']);
		}else{
			$m->addCondition('id',-1);
		}

		$grid->setModel($m);
		$grid->addPaginator(50);

		if($form->isSubmitted()){
			$view->js()->reload(array('dsa'=>$form['dsa']))->execute();
		}

	}
}