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
		$l = $this->api->locate('addons', __NAMESPACE__, 'location');
        $addon_location = $this->api->locate('addons', __NAMESPACE__);

        $this->api->pathfinder->addLocation(array(
            'js'=>'templates/js',
            'css'=>'templates/css'
            ))
            ->setBasePath($this->api->pathfinder->base_location->base_path.'/public/'.$addon_location)
            ->setBaseURL($this->api->pm->base_path.'/'.$addon_location);
		
		return parent::defaultTemplate();
	}

}