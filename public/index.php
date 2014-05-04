<?php

chdir('..');
// Un Comment following line if using with composer
// include 'vendor/autoload.php';

// Un Comment the following line if not using composer
include 'loader.php'; 

include 'lib/Frontend.php';	
$api = new Frontend('xb');
$api->main(); 