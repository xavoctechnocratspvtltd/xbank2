<?php

class page_test extends Page {
	function init(){
		parent::init();
		$time = strtotime("2014-08-05");
//or
		// $time = strtotime("2014-08-01");
		$first_day_of_week = date('Y-m-d', strtotime('Last Monday', $time));
		$last_day_of_week = date('Y-m-d', strtotime('Next Sunday', $time));
		echo $first_day_of_week;
		echo "<br/>";
		echo $last_day_of_week;
	}
}