<?php

class Frontend extends ApiFrontend {
    public $title ="xBank";
    public $now;
    public $today;
    public $current_branch; 
    public $current_staff; 
    public $current_member; 

    function init()
    {
        parent::init();

        // config-default.php file is placed at root of project folder
        $this->api->dbConnect();
        $this->title =  'Bhawani Credit Co-Operative Society Ltd.';

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
        
        $l=$this->add('Layout_Fluid');
        
        $footer=$l->addFooter();
        $this->header = $header=$l->addHeader();
        
        date_default_timezone_set('Asia/Kolkata');

        $this->today = date('Y-m-d',strtotime($this->recall('current_date',date('Y-m-d'))));
        $this->now = date('Y-m-d H:i:s',strtotime($this->recall('current_date',date('Y-m-d H:i:s'))));
        $this->jui->addStaticStylesheet('hindi');

        $mode= $this->recall('login_mode',null);

        if($mode=='dsa' || strpos($this->page,'dsa_') === 0){
            $auth = $this->add('BasicAuth');
            $auth->allowPage(array('corrections'));
            $auth->setModel('DSA','username','password');
            $auth->check();
            
            $dsa_menu=$header->add('Menu')->addClass('mymenu');
            $dsa_menu->addMenuItem('dsa_logout','Logout');
            $this->memorize('login_mode','dsa');

        }elseif($mode=='member' || strpos($this->page,'member_') === 0){
            
            $auth = $this->add('BasicAuth');
            $auth->allowPage(array('corrections'));
            $auth->setModel('Member','username','password');
            $auth->check();
            
            $member_menu=$header->add('Menu')->addClass('mymenu');
            $member_menu->addMenuItem('member_logout','Logout');
            $this->memorize('login_mode','member');
                        
        }elseif($mode=='agent' || strpos($this->page,'agent_') === 0){
            
            $auth = $this->add('BasicAuth');
            $auth->allowPage(array('duesms'));
            $auth->setModel('Agent','username','password');
            $auth->check();

            $agent_menu=$header->add('Menu')->addClass('mymenu');
            $agent_menu->addMenuItem('logout','Logout');
            $this->memorize('login_mode','agent');
        }elseif($mode == "dealer" || strpos($this->page,'dealer_') === 0){
            $auth = $this->add('BasicAuth');
            // $auth->allowPage(array('duesms'));
            $auth_model = $this->add('Model_Dealer');
            $auth_model->addCondition('ActiveStatus',true);
            $auth->setModel($auth_model,'username','password');
            $auth->check();

            $this->app->dealer_menu = $dealer_menu = $header->add('Menu')->addClass('mymenu');
            $dealer_menu->addMenuItem('dealer_logout','Logout')->addClass("atk-swatch-red");
            $this->memorize('login_mode','dealer');
        }else{
            $this->memorize('login_mode','staff');
            $staff_model = $this->add('Model_Staff');
            $staff_model->addCondition('is_active',true);
            $staff_model->addExpression('branch_login_allow')->set($staff_model->refSQL('branch_id')->fieldQuery('allow_login'));
            $staff_model->addCondition('branch_login_allow',true);
            $auth = $this->add('BasicAuth');
            $auth->allowPage(array('duesms','install'));
            $auth->setModel($staff_model,'username','password');
            $auth->check();

            if(!in_array($this->page, ['install','duesms'])){

                if(!$auth->model->tryLoad($auth->model->id)->loaded()){
                    $this->api->redirect($this->api->url('logout'));
                }

                $this->currentStaff = $this->current_staff = $auth->model;

                $this->currentBranch = $this->current_branch = $this->auth->model->ref('branch_id');
                $this->title = ' :: [' . $this->api->current_branch['name'].']';
                // die('Hi');
                $header_menu1=$header->add('Menu_Base')->addClass('mymenu');
            }

            if(strpos($this->page,'reports_') === 0){
                $acl = $this->add('Model_StaffReportAcl')
                ->addCondition('page',$this->page)
                ->addCondition('staff_id',$auth->model->id)
                ->tryLoadAny();
                ;
                if(!$acl->loaded()) $acl->save();
                if(!$acl['is_allowed']){
                    $this->page='notauthorised';
                }
            }
        }
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

    function addDateDuration($duration,$date=null){
        if(!$date) $date = $this->api->today;
        $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " ".$duration));    
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

    function isMonthLastDate($date=null){
        if(!$date) $date = $this->api->now;

        $date = date('Y-m-d',strtotime($date));

        return strtotime($date) == strtotime($this->monthLastDate());

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

    function subtractMonth($date,$month=1){
        if(!$date) $date=$this->api->today;

        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -".$month." MONTH"));
    }

    function get_months($date1, $date2) { 
       $time1  = strtotime($date1); 
       $time2  = strtotime($date2); 
       $my     = date('n-Y', $time2); 
       $mesi = array('01','02','03','04','05','06','07','08','09','10','11','12');

       //$months = array(date('F', $time1)); 
       $months = array(); 
       $f      = ''; 

       while($time1 < $time2) { 
          if(date('n-Y', $time1) != $f) { 
             $f = date('n-Y', $time1); 
             if(date('n-Y', $time1) != $my && ($time1 < $time2)) {
                $str_mese=$mesi[(date('n', $time1)-1)];
                $months[] = date('Y', $time1)."-".$str_mese; 
             }
          } 
          $time1 = strtotime((date('Y-n-d', $time1).' +15days')); 
       } 

       $str_mese=$mesi[(date('n', $time2)-1)];
       $months[] = date('Y', $time2)."-".$str_mese; 
       return $months; 
    } 

    function get_date_ranges($date1,$date2){
        $months_list = $this->get_months($date1, $date2);
        $start=true;
        $date_ranges=[];
        foreach ($months_list as $ml) {
            if($start){
                $date_ranges[]=['start'=>$date1,'end'=>date('Y-m-t',strtotime($date1))];
                $start=false;
            }elseif($ml==$months_list[count($months_list)-1]) {
                // It is last
                $date_ranges[]=['start'=>date('Y-m-01',strtotime($date2)),'end'=>$date2];
            }else{
                $date_ranges[]=['start'=>$ml.'-01','end'=>date('Y-m-t',strtotime($ml.'-01'))];
            }
        }

        return $date_ranges;
    }

    function markProgress($what, $running=0, $detail=null, $total=null){
        $data =$this->api->recall('progress_data',array());
        
        if($running !== null or $running !== "") $data[$what] = array('running'=>$running);
        if($detail) $data[$what] += array('detail'=>$detail);
        if($total) $data[$what] += array('total'=>$total. ' ' . (memory_get_peak_usage(true)/1024/1024) . "MiB" );

        if($running === null and isset($data[$what]))
            unset($data[$what]);

        $this->api->memorize('progress_data',$data);

        if(isset($this->app->sse_stream)){

            // $send_queue_in_every_x_requests = 1;

            // if(!isset($this->app->stream_send_queue)) $this->app->stream_send_queue = 0;
            // $this->app->stream_send_queue++;
            // if($this->app->stream_send_queue % $send_queue_in_every_x_requests == 0 )
                $this->app->sse_stream->jsEval($this->js()->univ()->updateProgress($data));
        }

        // $m=new Memcache();
        // $m->addServer('localhost',11211);
        // $m->set('data',$data);
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
        
        function addSharedLocations(){
        
        }

        function defaultTemplate(){
            return array('shared');
        }

}
