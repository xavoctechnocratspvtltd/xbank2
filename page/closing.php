<?php

class page_closing extends Page {

	function page_index(){
		if($_GET['do']){
			$this->api->forget('progress_data');
			try{
				$this->api->db->dsql()->owner->beginTransaction();
				foreach ($b=$this->add('Model_Branch')->addCondition('PerformClosings',true) as $branch_array) 
					$b->performClosing('2014-06-30');
				$this->api->db->dsql()->owner->rollBack();
				// $this->api->db->dsql()->owner->commit();
			}catch(Exception $e){
				$this->api->db->dsql()->owner->rollBack();
				throw $e;
			}
		}else{

			$m=new Memcache();
			$m->addServer('localhost',11211);
			$data=$m->get('data');

			$no = $this->api->recall('sno',1);
			$v = $this->add('View');
			$v->set($no. ' :: '.json_encode($data));
			$v->js(true)->reload(null,null,null,2000);
			$this->api->memorize('sno',$no+1);			
		}
	}

	function page_do(){
		if(!$_GET['cut_page']){
		}
	}
}