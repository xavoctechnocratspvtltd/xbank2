<?php
class page_sms extends \Page{
	function init(){
		parent::init();
		// echo $this->app->getConfig('xyz');

		$name="Devendra Nagda";
		$message="Dear ".$name.", your Bulk SMS Service Testing SuccessFully by Vijay mali";
		$no="8003597803";
		$sms=$this->add('Controller_Sms');
		$sms->sendMessage($no,$message);

	}
}