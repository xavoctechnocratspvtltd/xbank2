<?php

// DO NOT ADD ANYTHING TO THIS FILE!!

// This is a catch-all file for your project. You can change
// some of the values here, which are going to have affect
// on your project
// error_reporting(E_ALL);
$session_var='xbank2';
if(isset($_GET['page'])){
	$page=$_GET['page'];
	$page=str_replace("/", "_", $page);
	if(strpos($page,'branch_') !==false){
		$session_var='branch';
	}elseif(strpos($page,'system_') !==false){
		$session_var='system';
	}
}
include 'atk4/loader.php';
$api=new Frontend($session_var);
$api->main();