<?php


class page_utility_printaccountcontent extends Page {

	public $title='Print Contents for Accounts';

	function page_index(){

		$content_files = $this->add('Model_ContentFile');

		$form = $this->add('Form');
		$form->addField('Text','account_number')->setFieldHint('Comma separated account numbers');
		$form->addField('DropDown','content_file')->setEmptyText('Please select content file')->validateNotNull()->setModel($content_files);
		$form->addSubmit('Print');

		if($form->isSubmitted()){
			$this->app->memorize('u_content_print_data',json_encode($form->get()));
			$this->js()->univ()->location($this->app->url('./print',['cut_page'=>1,'form_values'=>json_encode($form->get())]))->execute();
		}
	}

	function page_print(){
		$form_values = $_GET['form_values'];
		$form_values = json_decode($form_values,true);
		$account_numbers = array_map(function($val) { return trim($val) ;} , explode(",", $form_values['account_number']));
		$this->app->forget('u_content_print_data');

		// $this->app->forget('content_print_data');
		// var_dump($form_values);
		$content_file = $this->add('Model_ContentFile')->load($form_values['content_file']);//->getAccountModel();
		$content = $content_file->getContent();
		$template = $this->add('GiTemplate');
		$template->loadTemplateFromString($content);

		foreach ($account_numbers as $acid) {
			if(!$acid) continue;
			$account_model = $content_file->getAccountModel($acid);
			// echo "<pre>";
			// print_r($account_model->get());
			$template->setHTML($account_model->get());
			echo $template->render();
			echo "<p style='page-break-before: always'></p>";
		}

	}

}