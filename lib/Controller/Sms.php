<?php

class Controller_Sms extends AbstractController{
	function sendActivationCode($model,$code){

	}

	function sendMessage($no,$msg){
		$curl=$this->add('Controller_CURL');
		$msg=urlencode($msg);
		
		// $url="http://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage&send_to=$no&msg=$msg&msg_type=TEXT&userid=2000137240&auth_scheme=plain&password=L0UrYNwjh&v=1.1&format=text";
		$url="http://cloud.smsindiahub.in/vendorsms/pushsms.aspx?user=".$this->app->getConfig('user')."&password=".$this->app->getConfig('password')."&msisdn=$no&sid=".$this->app->getConfig('senderid')."&msg=$msg&fl=0 ";
		// echo $url;
		return $curl->get($url);
	}
}