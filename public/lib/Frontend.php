<?php

class Frontend extends ApiFrontend {
    public $title ="xBank";
    public $now;
    public $today;
    public $current_branch; 

    function init()
    {
        parent::init();
        // config-default.php file is placed at root of project folder
        $this->api->dbConnect();
        $this->title =  'Agiletoolkit 4.3';
        $this->add('jUI');

        $this->pathfinder->addLocation(array(
            'addons'=>'atk4-addons'
            ))->setBasePath($this->pathfinder->base_location->base_path);


        $this->pathfinder->addLocation(array(
            'php'=>'lib'
            ))->setBasePath($this->pathfinder->base_location->base_path .'/shared');

        $this->pathfinder->addLocation(array(
            'php'=>'lib',
            'js'=>'atk4/js',
            'css'=>'atk4/css'
            ))->setBasePath($this->pathfinder->base_location->base_path .'/public');


        $l=$this->add('Layout_Fluid');
        
        $footer=$l->addFooter();
        $header=$l->addHeader();

        $auth = $this->add('BasicAuth');
        $auth->setModel('Staff','name','password');
        $auth->check();

        $this->current_branch = $this->auth->model->ref('branch_id');

        $header_menu1=$header->add('Menu_Base');

        // $header_menu1->addMenuItem('index',array('Home','icon'=>'home','swatch'=>'yellow'));

        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));

    }

    function set_date($date){
        $this->api->memorize('current_date',$date);
        $this->now = $date;
        $this->today = date('Y-m-d',strtotime($date));
    }

    function nextDate($date=null){
        if(!$date) $date = $this->api->today;
        $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 DAY"));    
        return $date;
    }
}
