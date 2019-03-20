<?php

class page_dealer_logout extends Page{
	function init(){
		parent::init();

		$this->api->destroySession();
		$this->api->auth->logout();
		$this->api->redirect('dealer_dashboard');
	}
}