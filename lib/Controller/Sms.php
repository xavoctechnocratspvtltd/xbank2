<?php

class Controller_Sms extends AbstractController{
	function sendActivationCode($model,$code){

	}

	function sendMessage($no,$msg){
		if(!$this->app->getConfig("send_sms",true)) return $no.' '. $msg.'<br/>';
		$curl=$this->add('Controller_CURL');
		$msg=urlencode($msg);
		$password = urlencode($this->app->getConfig('password'));
		$url="http://cloud.smsindiahub.in/vendorsms/pushsms.aspx?user=".$this->app->getConfig('user')."&password=".$password."&msisdn=$no&sid=".$this->app->getConfig('senderId')."&msg=$msg&fl=0&gwid=2";
		// echo $url;
		return $curl->get($url);
	}
}