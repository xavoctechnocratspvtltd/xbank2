<?php


class page_contentmanagement extends Page {
	
	public $title = "File Content Editor";

	function init(){
		parent::init();	

		$this->add('Controller_Acl');

		$file = $this->app->stickyGET('file');
		$file_path = 'templates/contents/'.$file;

		$content_array = [
			'societyandlegalnotice'=>'Society And Legal Notice',
			'envelop_address'=>'Envelop Address',
		];


		$page_selector_form = $this->add('Form');
		$page_selector_form->addField('DropDown','file')->setValueList($content_array)->set($file)->setEmptyText('Please select file');
		$page_selector_form->addSubmit('Edit');

		$edit_form = $this->add('Form');
		$file_field = $edit_form->addField('RichText','content');
		if(file_exists($file_path)){
			$file_str = file_get_contents($file_path);
			$file_field->set($file_str);
		}

		$edit_form->addSubmit('Save');

		if($edit_form->isSubmitted()){
			file_put_contents($file_path, $edit_form['content']);
			$this->js()->univ()->location($this->app->url(null,['file'=>$file]))->execute();
		}

		if($page_selector_form->isSubmitted()){
			$this->js()->univ()->location($this->app->url(null,['file'=>$page_selector_form['file']]))->execute();
		}
	}
}