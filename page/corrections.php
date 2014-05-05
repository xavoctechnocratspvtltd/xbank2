<?php

class page_corrections extends Page {
	$rename_fields=array();// [] = array(table_name,old_field,new_field)
	
	function init(){
		parent::init();

		$renameFields =array(
				array('members','Name','name'),
			);

		$new_field=array(
				array('table_name','field_name','type')
			);

		$remove_fields=array(
				array('table_name','field_name')
			);

	}

	function hasField($table,$field){

	}

	function removeField($table,$field){
		
	}

	function renameFields(){

	}
}