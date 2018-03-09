<?php


class page_notauthorised extends Page {
	function init(){
		parent::init();

		$this->add('View_Error')->set('You are not authorised to view this page');
	}
}