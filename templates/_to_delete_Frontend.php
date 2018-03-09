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

        $this->pathfinder->addLocation(array(
            'php'=>'lib',
            'js'=>'atk4/js',
            'css'=>'atk4/css',
            'template'=>'templates',
            ))->setBasePath($this->pathfinder->base_location);
        
        $this->addLocation(array(
            'js'=>'atk4/public/atk4/js'
            ))->setParent( $this->pathfinder->base_location );

        $this->addLocation(array(
                'addons'=>array( 'xbank-addons', 'atk4-addons' ))
        )->setParent( $this->pathfinder->base_location );


        $this->add('jUI');
        // $l=$this->add('Layout_Fluid');
        
        // $footer=$l->addFooter();
        // $header=$l->addHeader();

        // if($this->page !='corrections'){

        //     $auth = $this->add('BasicAuth');
        //     $auth->allowPage(array('corrections'));
        //     $auth->setModel('Staff','username','password');
        //     $auth->check();

        //     $this->currentBranch = $this->current_branch = $this->auth->model->ref('branch_id');
        //     $this->title = ' :: [' . $this->api->current_branch['name'].']';
        // }

        // $this->jui->addStaticStylesheet('hindi');

        // $header_menu1->addMenuItem('index',array('Home','icon'=>'home','swatch'=>'yellow'));

        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));
        
        // $header_menu1=$header->add('Menu_Base')->addClass('mymenu');

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

    function previousDate($date=null){
        if(!$date) $date = $this->api->today;
        $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -1 DAY"));    
        return $date;
    }

    function monthFirstDate($date=null){
        if(!$date) $date = $this->api->now;

        return date('Y-m-01',strtotime($date));
    }

    function monthLastDate($date=null){
        if(!$date) $date = $this->api->now;

        return date('Y-m-t',strtotime($date));
    }

    function nextMonth($date=null){
        if(!$date) $date=$this->api->today;

        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 MONTH"));
    }

    function previousMonth($date=null){
        if(!$date) $date=$this->api->today;

        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -1 MONTH"));
    }

    function nextYear($date=null){
        if(!$date) $date=$this->api->today;

        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 YEAR"));
    }

    function previousYear($date=null){
        if(!$date) $date=$this->api->today;

        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -1 YEAR"));
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

    function getFinancialQuarter($date=null,$start_end = 'both'){
        if(!$date) $date = $this->api->today;

        $month = date('m',strtotime($date));
        $year = date('Y',strtotime($date));
        
        switch ($month) {
            case 1:
            case 2:
            case 3:
                $q_month_start='-01-01';
                $q_month_end='-03-31';
                break;
            case 4:
            case 5:
            case 6:
                $q_month_start='-04-01';
                $q_month_end='-06-30';
                break;
            case 7:
            case 8:
            case 9:
                $q_month_start='-07-01';
                $q_month_end='-09-30';
                break;
            case 10:
            case 11:
            case 12:
                $q_month_start='-10-01';
                $q_month_end='-12-31';
                break;
        }

        
        if(strpos($start_end, 'start') !== false){
            return $year.$q_month_start;
        }
        if(strpos($start_end, 'end') !== false){
            return $year.$q_month_end;
        }

        return array(
                'start_date'=>$year.$q_month_start,
                'end_date'=>$year.$q_month_end
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

    function getComission($CommissionString, $whatToGet,$PremiumNumber=-1){
            $CommissionString=trim($CommissionString," ");
            $CommissionString=trim($CommissionString,",");
            $commArray=explode(",",$CommissionString);
            $result=array();
            $result["isPercentage"]=false;
            if(strpos("%", $commArray[0]) !== false ){
                $result["isPercentage"]=true;
//                $commArray[0]=str_replace("%", "");
            }
            switch($whatToGet){
                case OPENNING_COMMISSION:
                    $result["Commission"]=trim($commArray[0]);

                    break;
                case PREMIUM_COMMISSION:
                    if(count($commArray)  <= 1){
                        throw new Exception("Premium must start from second Commission");
                    }

                    if($PremiumNumber >= count($commArray)){
                        $commArray[$PremiumNumber]=$commArray[count($commArray)-1];
                    }

                    if(strpos("%", $commArray[$PremiumNumber]) !== false ){
                        $result["isPercentage"]=true;
//                        $commArray[$PremiumNumber]=str_replace("%", "");
                    }

                    $result["Commission"]=trim($commArray[$PremiumNumber]);
                    $result["isPercentage"]=true;
                    break;
            }

            return $result["Commission"];
        }

        function defaultTemplate(){
            return array('shared');
        }

}
