<?php

class page_reports_deposit_comparativebusiness extends Page {
	public $title ="Comparative Business Reports";
	
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$agent_tab = $tabs->addTab('Agent Base');
		$mo_tab = $tabs->addTab('MO Base');

		$this->manageAgentBase($agent_tab);
		$this->manageMoBase($mo_tab);
	}

	function manageAgentBase($tab){
		$form = $tab->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();

		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');

		$form->addSubmit('Go');
		$data=[];
		$account_types = ['Recurring','FixedAndMis','DDS2'];
		if($_GET['from_date']){
			$date_range = $this->get_date_ranges($_GET['from_date'],$_GET['to_date']);
			$agents = $this->add('Model_Agent');
			if($_GET['agent']){
				$agents->addCondition('id',$_GET['agent']);
			}

			foreach ($agents as $ag) {
				foreach ($date_range as $dr) {

					$data_row=['agent'=>$ag['agent_member_name'],'start'=>$dr['start'],'end'=>$dr['end']];
					
					foreach ($account_types as $type) {
							
						$accounts = $this->add('Model_Account_'.$type);
						$accounts->addCondition('agent_id',$ag->id);
						$accounts->addCondition('created_at','>=',$dr['start']);
						$accounts->addCondition('created_at','<',$this->app->nextDate($dr['end']));
						$count = $accounts->count()->getOne();
						$amount_sum = $accounts->sum('Amount')->getOne();
						$data_row['new_'.$type] = $count;
						$data_row['amount_'.$type] = $amount_sum;
					}
					
					$tra_row = $this->add('Model_TransactionRow');
					$acc_j = $tra_row->join('accounts','account_id');
					$acc_j->addField('agent_id');
					$scheme_j = $acc_j->join('schemes','scheme_id');
					$scheme_j->addField('SchemeType');

					$tra_row->addCondition('created_at','>=',$dr['start']);
					$tra_row->addCondition('created_at','<',$this->app->nextDate($dr['end']));
					$tra_row->addCondition('transaction_type',TRA_RECURRING_ACCOUNT_AMOUNT_DEPOSIT);
					$tra_row->addCondition('agent_id',$ag->id);
					$tra_row->addCondition('SchemeType','Recurring');

					$data_row['rd_collection_accounts'] = $tra_row->_dsql()->del('fields')->field('COUNT(DISTINCT(account_id))')->getOne();
					$data_row['rd_collection_amount'] = $tra_row->sum('amountCr')->getOne();

					$data[]= $data_row;

				}
			}
		}

		$grid = $tab->add('Grid');

		$grid->setSource($data);

		$grid->addColumn('start');
		$grid->addColumn('end');
		$grid->addColumn('agent');

		foreach ($account_types as $type) {
			$grid->addColumn('new_'.$type);
			$grid->addColumn('amount_'.$type);
		}

		$grid->addColumn('rd_collection_accounts');
		$grid->addColumn('rd_collection_amount');

		if($form->isSubmitted()){
			$grid->js()->reload(['from_date'=>$form['from_date'],'to_date'=>$form['to_date'],'agent'=>$form['agent']])->execute();
		}
	}


	function manageMoBase($tab){

		$form = $tab->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();

		$mo_field = $form->addField('autocomplete\Basic','mo');
		$mo_field->setModel('Model_Mo');
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

}