<?php


class Form_Field_Member extends autocomplete\Form_Field_Basic{
	function init(){
		parent::init();

		$member_field_view = $this->other_field->belowField()->add('View');
		if($_GET['selected_member_id']){
			$msg="";
			$member = $this->add('Model_Member')->load($_GET['selected_member_id']);
			$msg .= $member['is_defaulter']?" Defaulter ":" Clear ";

			$member_field_view->set($msg);
		}
		$this->other_field->js('change',$member_field_view->js()->reload(array('selected_member_id'=>$this->js()->val())));
	}
}