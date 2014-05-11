<?php

class xCRUD extends View_CRUD {

	function init(){
		parent::init();
		if($this->grid){
			$this->grid->addQuickSearch(array('AccountNumber'));
		}
	}

	function formSubmit($form){
		try {
			if($this->hook('myupdate',array($form))){
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