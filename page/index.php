<?php

class page_index extends xPage{
	public $title ="Dashboard";
	
	function init(){
		parent::init();
			
		$model = $this->add('Model_Active_Account_Loan')->getAllForPaneltyPosting();
		$g=$this->add('Grid');
		$g->setModel($model,array('name','due_panelty'));
		$g->addPaginator(50);

	}
}