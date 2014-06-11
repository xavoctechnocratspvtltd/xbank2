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
            'addons'=>'my-addons',
            ))->setBasePath($this->pathfinder->base_location->base_path.'/public');


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

        if($this->page !='corrections'){

            $auth = $this->add('BasicAuth');
            $auth->allowPage(array('corrections'));
            $auth->setModel('Staff','username','password');
            $auth->check();

            $this->current_branch = $this->auth->model->ref('branch_id');
            $this->title = ' :: [' . $this->api->current_branch['name'].']';
        }

        
        $header_menu1=$header->add('Menu_Base');

        // $header_menu1->addMenuItem('index',array('Home','icon'=>'home','swatch'=>'yellow'));

        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));

    }

    function setDate($date){
        $this->api->memorize('current_date',$date);
        $this->now = date('Y-m-d H:i:s',strtotime($date));
        $this->today = date('Y-m-d',strtotime($date));
    }

    function nextDate($date=null){
        if(!$date) $date = $this->api->today;
        $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 DAY"));    
        return $date;
    }

    function getFinancialYear($date=null,$start_end = 'both'){
        if(!$date) $date = $this->api->now;
        $month = date('m',strtotime($date));
        $year = date('Y',strtotime($date));
        if($month >=1 AND $month <=3  ){
            $f_year_start = $year-1;
            $f_year_end = $year;
        }
        else{
            $f_year_start = $year;
            $f_year_end = $year+1;
        }

        if(strpos($start_end, 'start') !==false){
            return $f_year_start.'-04-01';
        }
        if(strpos($start_end, 'end') !==false){
            return $f_year_end.'-03-31';
        }

        return array(
                'start_date'=>$f_year_start.'-04-01',
                'end_date'=>$f_year_end.'-03-31'
            );

    }

    function my_date_diff($d1, $d2){
        $d1 = (is_string($d1) ? strtotime($d1) : $d1);
        $d2 = (is_string($d2) ? strtotime($d2) : $d2);

        $diff_secs = abs($d1 - $d2);
        $base_year = min(date("Y", $d1), date("Y", $d2));

        $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
        return array(
        "years" => date("Y", $diff) - $base_year,
        "months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
        "months" => date("n", $diff) - 1,
        "days_total" => floor($diff_secs / (3600 * 24)),
        "days" => date("j", $diff) - 1,
        "hours_total" => floor($diff_secs / 3600),
        "hours" => date("G", $diff),
        "minutes_total" => floor($diff_secs / 60),
        "minutes" => (int) date("i", $diff),
        "seconds_total" => $diff_secs,
        "seconds" => (int) date("s", $diff)
        );
    }

    function markProgress($what, $running=0, $detail=null, $total=null){
        $data =$this->api->recall('progress_data',array());
        
        if($running !== null or $running !== "") $data[$what] = array('running'=>$running);
        if($detail) $data[$what] += array('detail'=>$detail);
        if($total) $data[$what] += array('total'=>$total);

        if($running === null and isset($data[$what]))
            unset($data[$what]);

        $this->api->memorize('progress_data',$data);

        $m=new Memcache();
        $m->addServer('localhost',11211);
        $m->set('data',$data);
    }

    function getProgress(){
        $m=new Memcache();
        $m->addServer('localhost',11211);
        $data=$m->get('data');
        return $data;
    }

    function resetProgress(){
        $this->api->memorize('progress_data',array());
    }

}
