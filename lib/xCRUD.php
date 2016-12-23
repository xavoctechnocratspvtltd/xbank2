<?php

class xCRUD extends CRUD {

	public $form_class='Form_Stacked';
	public $add_form_beautifier=true;
	
	function setModel($model,$f=null,$f2=null){
		parent::setModel($model,$f,$f2);
		
		if($this->add_form_beautifier)
			$this->add('Controller_FormBeautifier');
	}

	function formSubmit($form){
		try {
			$hook_value = $this->hook('myupdate',array($form));
			if($hook_value[0]){
	            $self = $this;
	            $this->api->addHook('pre-render', function () use ($self) {
	                $self->formSubmitSuccess()->execute();
	            });
				return;	
			}else{
				
				return parent::formSubmit($form);
			}
        } catch (Exception_ValidityCheck $e) {
            $form->displayError($e->getField(), $e->getMessage());
        }
		return false;		
	}
}