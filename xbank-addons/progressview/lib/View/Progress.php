<?php

namespace progressview;

class View_Progress extends \View{
	public $progress_views;
	public $interval=2000;	
	function init(){
		parent::init();
		$this->addClass('progress-base');
		$data = $this->api->getProgress();
		if($_GET['fetch_data']){
			echo json_encode($data);
			exit;
		}
		$this->js(true)->_load('progressview');
		$this->js(true)->univ()->setInterval('$.univ().updateProgress("'.$this->api->url(null,array('fetch_data'=>1)).'")', $this->interval);
	}

	function defaultTemplate(){
		$this->app->pathfinder->base_location->addRelativeLocation(
		    'xbank-addons/'.__NAMESPACE__, array(
		        'php'=>'lib',
		        'template'=>'templates',
		        'css'=>'templates/css',
		        'js'=>'templates/js',
		    )
		);
		
		return parent::defaultTemplate();
	}

}