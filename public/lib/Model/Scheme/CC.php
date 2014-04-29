<?php
class Model_Scheme_CC extends Model_Scheme {
	function init(){
		parent::init();

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function manageForm($form){
		if($form->isSubmitted()){
			$form->js()->univ()->successMessage('HI')->execute();
		}
	}

}