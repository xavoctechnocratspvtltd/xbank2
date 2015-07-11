<?php

class Grid_Report_TdsQuaterly extends Grid_AccountsBase{
	public $as_on_date;
	public $sno = 1;
	public $previous_array = array();
	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		// $this->addSno();
		$this->addSno();
		$paginator = $this->addPaginator(50);
		$this->skip_var = $paginator->skip_var;

		$this->addOrder()->move('s_no','first')->now();
		// $this->addFormatter('agent_id','Wrap');
	}
	
	function formatRow(){
		// if(!in_array($this->model['agent_id'],$this->previous_array)){
		// 	$this->previous_array[] = $this->model['agent_id'];
		// 	$agent_m = $this->add('Model_Agent')->tryLoad($this->model['agent_id']);				
		// 	$this->current_row['agent_id'] = $agent_m['name'];
		// 	$this->current_row['s_no'] = (($this->sno++) + ($_GET[$this->skip_var]));
		// }else
		// 	$this->current_row['agent_id'] = "";

		parent::formatRow();
	}
}