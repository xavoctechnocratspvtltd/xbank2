<?php

class page_dealer_dashboard extends Page{
	function init(){
		parent::init();

		$this->title = " ";
		$this->app->dealer_menu->addMenuItem('dealer_dashboard',$this->api->auth->model['name'])->addClass('atk-swatch-green');

		$this->app->dealer_menu->addMenuItem('dealer_loandispatch','Loan Dispatch');
		$this->app->dealer_menu->addMenuItem('dealer_rcduelist','RC Due List');
		$this->app->dealer_menu->addMenuItem('dealer_forclose','For Close Report');
		$this->app->dealer_menu->addMenuItem('dealer_emiduelist','EMI Due List');
		$this->app->dealer_menu->addMenuItem('dealer_accountdetail','Account Detail');
		
		if($this->app->page == "dealer_dashboard"){
			$this->add('View')->setElement('h3')->set('Welcome '.$this->api->auth->model['name'])->addClass('text-center');
			$this->add('View')->setElement('h3')->set('Date '.$this->app->today)->addClass('text-center');
		}
		

	}	
}