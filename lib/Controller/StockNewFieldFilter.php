<?php


class Controller_StockNewFieldFilter extends AbstractController {

	public $branch_field;
	public $container_field;
	public $container_row_field;

	function init(){
		parent::init();

		if($this->owner instanceof CRUD && $this->owner->isEditing()){
			$form = $this->owner->form;
		}elseif(! $this->owner instanceof FORM){
			throw new Exception("Add StockNewFieldFilter Controller to Form or CRUD only", 1);
		}else{
			$form = $this->owner;
		}

		if($this->container_field && $this->branch_field){
			$filter_key = $this->branch_field.'_for_'.$this->container_field;
			$branch_field = $form->getElement($this->branch_field);
			$container_field = $form->getElement($this->container_field);
			if($_GET[$filter_key]){
				$container_field->getModel()->addCondition('branch_id',$_GET[$filter_key]);
			}
			$branch_field->js('change',$form->js()->atk4_form('reloadField',$this->container_field,array($this->api->url(),$filter_key=>$branch_field->js()->val())));
		}

		if($this->container_row_field && $this->container_row_field){
			$filter_key = $this->container_field.'_for_'.$this->container_row_field;

			$container_field = $form->getElement($this->container_field);
			$container_row_field = $form->getElement($this->container_row_field);
			if($_GET[$filter_key]){
				$container_row_field->getModel()->addCondition('container_id',$_GET[$filter_key]);
			}
			$container_field->js('change',$form->js()->atk4_form('reloadField',$this->container_row_field,array($this->api->url(),$filter_key=>$container_field->js()->val())));

		}

	}
}