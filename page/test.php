<?php



class page_test  extends Page {
	function init(){
		parent::init();

		$on_date = '2017-02-28';
		$days = $this->api->my_date_diff($on_date,$this->app->previousDate('2017-01-31'));
		$days_to_count = $days['days_total'];

		if(date('m',strtotime($on_date))==2){
			// Its february
			if($days_to_count >= date("t",strtotime($on_date))) $days_to_count=30;
		}
		if($days_to_count >= 30) $days_to_count=30;

		echo date("t",strtotime($on_date)). ' '. $days_to_count;

		

	}
}
