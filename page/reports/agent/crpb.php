<?php

class page_reports_agent_crpb extends Page {
	public $title="Agent CRPB Reports";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$agent=$form->addField('autocomplete/Basic','agent');
		$qrtr=$form->addField('autocomplete/Basic','qrtr');

		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('For Close '. date('d-M-Y',strtotime($till_date))); 
		$data = array(
 					array( 's_no' => '1', 
 							'member_id_name' => 'Smith', 
 							'phone_no'=>'2000', 
 							'saving_account_no'=>'1000', 
 							'self_crpb'=>'12-09-100',
 							'team_crpb'=>'any',
 							'agent_code'=>'any',
 							'current_cadre'=>'1000', 
 							'total_team_member'=>'1000', 
 							'self_total_business'=>'1000', 
 							'team_total_business'=>'1000', 
 							'team_member_list'=>'1000', 
 						)
				);

		// $account_model->add('Controller_Acl');
		$grid->addColumn('s_no');
		$grid->addColumn('member_id_name');
		$grid->addColumn('phone_no');
		$grid->addColumn('saving_account_no');
		$grid->addColumn('self_crpb');
		$grid->addColumn('team_crpb');
		$grid->addColumn('agent_code');
		$grid->addColumn('current_cadre');
		$grid->addColumn('total_team_member');
		$grid->addColumn('self_total_business');
		$grid->addColumn('team_total_business');
		$grid->addColumn('team_member_list');

		$grid->setSource($data);

		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

}
