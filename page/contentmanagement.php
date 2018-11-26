<?php


class page_contentmanagement extends Page {
	
	public $title = "File Content Editor";

	function page_index(){

		$this->add('Controller_Acl');

		$crud = $this->add('CRUD',['allow_edit'=>false]);
		$crud->setModel('ContentFile');

		$crud->grid->addColumn('template','file_edit')->setTemplate('<a href="?page=contentmanagement_edit&file={$name}">EDIT</a>','file_edit');
	}

	function page_edit(){

		$this->add('View')->setElement('a')->setAttr('href',$this->app->url('../'))->set('Back');

		$file = $this->app->stickyGET('file');
		$file_path = 'templates/contents/'.$file;

		$this->title = "Edit ". $file;

		$edit_form = $this->add('Form');
		$file_field = $edit_form->addField('RichText','content');
		if(file_exists($file_path)){
			$file_str = file_get_contents($file_path);
			$file_field->set($file_str);
		}else{
			touch($file_path);
		}

		$edit_form->addSubmit('Save');

		$account_model = $this->add('Model_ContentFile')->getAccountModel();

		$fields = array_map(function($val) { return '{$'.$val.'} ' ;} , $account_model->getActualFields());
		$this->add('View')->set(implode(", ",$fields ));

		if($edit_form->isSubmitted()){
			file_put_contents($file_path, $edit_form['content']);
			$this->js()->univ()->location($this->app->url(null,['file'=>$file]))->execute();
		}
	}
}