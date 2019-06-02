<?php

class page_memorandum_report_gstr1 extends Page{
	public $title = "GSTR-1";

	function init(){
		parent::init();

		$this->add('View')->setElement('h3')->set('Inward GST Report');

		$this->filter = $this->app->stickyGET('filter')?:0;
		$this->from_date = $this->app->stickyGET('from_date')?:0;
		$this->to_date = $this->app->stickyGET('to_date')?:0;

		$form = $this->add('Form',null,null,['form/horizontal']);
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addSubmit('Filter');
		$form->add('misc\Controller_FormAsterisk');
		$this->add('View')->setElement('hr');

		$view = $this->add('View');
		if($this->filter){
			$view->add('View_GST_Gstr1',['from_date'=>$this->from_date,'to_date'=>$this->to_date]);
		}

		if($form->isSubmitted()){
			$view->js()->reload(['filter'=>1,'from_date'=>$form['from_date'],'to_date'=>$form['to_date']])->execute();
		}
	}
}