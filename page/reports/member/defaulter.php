<?php

class page_reports_member_defaulter extends Page{
	function init(){
		parent::init();

		$member_model=$this->add('Model_Member');
		$member_model->setOrder('created_at','desc');
		$member_model->addCondition('is_defaulter',true);

		$grid=$this->add('Grid',null,null,array('view/mygrid'));
		// $grid->add('H3',null,'grid_buttons')->set('Member Repo As On '. date('d-M-Y',strtotime($till_date))); 
		$grid->setModel($member_model,array('id','branch','name','CurrentAddress','tehsil','city','PhoneNos','created_at','is_active'));
		$grid->addPaginator(50);
		$grid->addQuickSearch(array('id','name','PhoneNos'));

		$grid->add('Controller_DocumentsManager',array('doc_type'=>'MemberDocuments'));
		// $grid->addColumn('expander','details');
		// $grid->addColumn('expander','accounts');
		// $grid->addColumn('expander','guarantor_in');
	}
}