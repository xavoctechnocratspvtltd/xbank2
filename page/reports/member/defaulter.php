<?php

class page_reports_member_defaulter extends Page{
	function init(){
		parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}
		
		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Member Defaulter List As On '. date('d-M-Y',strtotime($till_date))); 
		$member=$this->add('Model_Member');
		$member->addCondition('is_defaulter',true);
		$grid->setModel($member);
		$grid->addPaginator(50);

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);
	}
}