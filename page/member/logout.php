<?php

class page_member_logout extends Page{
	function init(){
		parent::init();

		$this->api->destroySession();
		$this->api->auth->logout();
		$this->api->redirect('member_dashboard');
	}
}