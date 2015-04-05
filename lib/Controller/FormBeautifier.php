<?php


class Controller_FormBeautifier extends AbstractController{
	public $param=array();
	public $form=false;

	public $header_type='panel';
	public $modifier='default';

	public $related_model = false;
	public $groups=array();
	public $running_group=false;
	public $running_cell=false;


	function init(){
		parent::init();


		if(!($this->owner instanceof CRUD) and !($this->owner instanceof Form)){
			throw $this->exception('Must be added on CRUD or Form')->addMoreInfo('Current Owner',$this->owner);
		}

		if($this->owner instanceof CRUD and $this->owner->isEditing()){
			$this->form = $this->owner->form;
		}

		if($this->owner instanceof Form){
			$this->form = $this->owner;
		}

		if(!$this->form) return;
		
		$this->related_model = $this->form->getModel();
		
		$this->order = $this->form->add('Order');

		foreach ($this->form->elements as $field) {
			if(!$field instanceof Form_Field) continue;
			if($this->setColumnCell($field)){
				$this->moveField($field);
			}
		}
		$this->order->now();
	}

	function setColumnCell($field){
		// 'a~6~New Panel start 1'
		// 'a~6'
		// 'a~6~bl' in below last column 
		// 'b~12~New Panel Start 2'

		$model_field = $field;
		if($this->related_model){
			if($this->related_model->hasElement($field->short_name))
				$model_field = $this->related_model->getElement($field->short_name);
		}

		$group = $model_field->setterGetter('group');
		$group_details = explode("~", $group);
		if(count($group_details)==1){
			// if($this->running_group)
				// $this->order->move($field,'after',$this->running_group);
			// return;
			$group_details=array('a'.rand(1000,9999),'12');
		} 
		// echo $field. ' ' .$group.' ';
		// echo "1 ";
		if(in_array($group_details[0], array_keys($this->groups))){
			// echo "2 ";
			$this->running_group = $this->groups[$group_details[0]];
		}else{
			// echo "3 ";
			$group_header = $this->form;
			if(count($group_details)==3 and $group_details[2]!='bl'){
				// echo "4 ";
				$group_header = $this->form->add('View');
				$group_header->addClass('panel panel-default');
				$group_header->add('H4')->setHTML($group_details[2])->addClass('panel-heading');
				$group_header = $group_header->add('View')->addClass('panel-body');
			}
			$this->running_group = $group_header->add('Columns');
			$this->groups[$group_details[0]]=$this->running_group;
		}

		if(count($group_details)==2 OR (count($group_details)==3 and $group_details[2] != 'bl')){
			// echo "5 ";
			$this->running_cell = $this->running_group->addColumn($group_details[1]);
		}

		// echo "6 <br/>";
		return true;
	}

	function moveField($field){
		$model_field = $field;
		if($this->related_model){
			if($this->related_model->hasElement($field->short_name))
				$model_field = $this->related_model->getElement($field->short_name);
		}
		if(isset($model_field->icon)){
			$icon = explode("~", $model_field->icon);
			$color='';
			if(isset($icon[1])) $color= "style=' color:".$icon[1]."'";
			$field->setCaption("<i class='".$icon[0]."' $color ></i> ".$model_field->caption());
		}

		if($field instanceof Form_Field_Upload){
			$field->js(true)->parent()->parent()->appendTo($this->running_cell);
			return;
		}
		if($field instanceof Form_Field_DatePicker){
			$field->js(true)->parent()->parent()->parent()->parent()->appendTo($this->running_cell);
			return;
		}

		if($field instanceof autocomplete\Form_Field_Basic){
			// $field->other_field->js(true)->parent()->parent()->css('border','2px solid red');
			// $field->other_field->js(true)->parent()->parent()->appendTo($this->running_cell);
			$field->js(true)->parent()->parent()->appendTo($this->running_cell);
			$field->other_field->append_to = $this->running_cell;
			return;
		}

		$field->js(true)->parent()->parent()->appendTo($this->running_cell);
		// $this->running_cell->add($field);
	}

}


// foreach ($model_fields->elements as $position => $fld) {
// 			if(!($fld instanceof Field)) {
// 				continue;
// 			}

// 			// if fld->group is same as last group and last group is not ""
// 			if(isset($fld->group))
// 				$group_details = explode("~", $fld->group);
// 			else
// 				$group_details = array();

// 			// echo $fld->short_name .' in form ?? ' . $this->form->hasField($fld->short_name). '<br/>';
// 			if(count($group_details) < 2 or !($elm=$this->form->hasElement($fld->short_name))){
// 				if($elm=$this->form->hasElement($fld->short_name)){
// 					$last_form_field=$elm;
// 					$elm->addClass('form-control');
// 					if(isset($fld->icon)){
// 						$icon = explode("~", $fld->icon);
// 						$color='';

// 						if(isset($icon[1])) $color= "style=' color:".$icon[1]."'";

// 						$elm->setCaption(array($fld->caption(),'swatch'=>'red'));
// 					}
// 				}
// 				continue;
// 			}

// 			// beautify field by adding bootstrap classes
// 			$elm->addClass('form-control');
// 			if(isset($fld->icon)){
// 				$icon = explode("~", $fld->icon);
// 				$color='';

// 				if(isset($icon[1])) $color= "style=' color:".$icon[1]."'";

// 				$elm->setCaption("<i class='".$icon[0]."' $color ></i> ".$fld->caption());
// 			}

// 			if($group_details[0]!= $last_group){
// 				if(isset($group_details[2]) and $group_details[2]!='bl'){
// 					$group_header = $this->form->add('View');
// 					$group_header->addClass('panel panel-'. $this->modifier);
// 					// $fieldset = $running_cell->add('View');//->setElement('fieldset');
// 					$group_header->add('H4')->setHTML($group_details[2])->addClass('panel-heading');
// 					$running_cell = $group_header->add('View')->addClass('panel-body');
// 					$running_column = $running_cell->add('Columns');
// 					$move = $group_header;
// 				}else{
// 					if(isset($group_details[2]) and $group_details[2] !='bl'){
// 						$running_cell = $running_column->addColumn(12);
// 						$group_header = $running_cell->add('View');
// 						$group_header->addClass('panel panel-'.$this->modifier);
// 						// $fieldset = $running_cell->add('View');//->setElement('fieldset');
// 						$group_header->add('H4')->setHTML($group_details[2])->addClass('panel-heading');
// 						$container = $group_header->add('View')->addClass('panel-body');
// 						$running_column = $running_cell->add('Columns');
// 						$running_cell = $running_column->addColumn($group_details[1]);
// 					}else{
// 						$running_column = $this->form->add('Columns');
// 					}
// 					$move = $running_column;
// 				}

// 				if(!$last_form_field){
// 					$order->move($move,'first');
// 				}else{
// 					$order->move($move,'after',$elm->short_name);
// 				}
// 			}

// 			if(isset($group_details[2]) and $group_details[2] == 'bl'){ // Bellow Last Column
// 				$running_cell->add($elm);
// 			}else{
// 				$running_cell = $running_column->addColumn($group_details[1]);
// 				$running_cell->add($elm);
// 			}

// 			$last_group = $group_details[0];
// 			$last_form_field = $elm;

// 			// create a new columns object
// 			// if same as last group
// 			// add Column to column object and move object into it
// 		}