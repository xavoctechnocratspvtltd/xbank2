<?php

class page_dsa_logout extends Page{
	function init(){
		parent::init();

		$this->api->destroySession();
		$this->api->auth->logout();
		$this->api->redirect('dsa_dashboard');
	}
}