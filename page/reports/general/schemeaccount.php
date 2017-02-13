<?php

class page_reports_general_schemeaccount extends Page {
	public $title="Account Close Repots";
	function page_index(){
		// parent::init();

		$from_date = '1970-01-02';
		$to_date = $this->api->today;
		
		$scheme_m = $this->add('Model_Scheme');

		// if($_GET['acc_type']){
		// 	$scheme_m->addCondition('SchemeType',$_GET['acc_type']);
		// }

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','status')->setValueList(array('all'=>'All','0'=>'InActive','1'=>'Active'));
		$account_type=$form->addField('DropDown','account_type');
		$array_value = $array_key = explode(',', ACCOUNT_TYPES);
		$account_type->setValueList(array_combine($array_key, $array_value))->setEmptyText('Select Account type');
		$scheme_field=$form->addField('DropDown','scheme');
		$scheme_field->setModel($scheme_m);
		
		// $account_type->js('change',
		// 			$scheme_field->js()->reload(
		// 					array('acc_type'=>$account_type->js()->val())));

		$form->addSubmit('GET List');

		$grid=$this->add('Grid',array('from_date'=>$from_date,'to_date'=>$to_date));

		$grid->add('H3',null,'grid_buttons')->set('Scheme Wise Account Report From Date '. date('d-M-Y',strtotime($from_date)).'To Date '.date('d-M-Y',strtotime($to_date)) ); 
		
		$scheme = $this->add('Model_Scheme');

		$scheme->addExpression('account_count')->set(function($m,$q){
			return $m->add('Model_Account')
					->addCondition('scheme_id',$q->getField('id'))
					->count();
		});
		$scheme->addExpression('sum_amount')->set(function($m,$q){
			return $m->add('Model_Account')
					->addCondition('scheme_id',$q->getField('id'))
					->sum('Amount');
		});
		
		$filter = $this->api->stickyGET('filter');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$selected_scheme = $this->api->stickyGET('scheme');
		$status = $this->api->stickyGET('status');

		if($_GET['filter']){
			if($account_type){
				$selected_account_type = $this->api->stickyGET('account_type');
				$scheme->addCondition('SchemeType',$selected_account_type);
			}
			if($selected_scheme){
				$scheme->addCondition('id',$selected_scheme);
			}
			if($_GET['from_date']){
				$scheme->addCondition('created_at','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$scheme->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}
			if($_GET['status'] !=='all'){
				$scheme->addCondition('ActiveStatus',$_GET['status']==0?false:true);
			}
		}
		// $scheme->add('Controller_Acl');
		$grid->setModel($scheme,array('name','account_count','sum_amount'));
		
		if($form->isSubmitted()){
			$grid->js()->reload(
						array(
								'to_date'=>$form['to_date']?:0,
								'from_date'=>$form['from_date']?:0,
								'account_type'=>$form['account_type'],
								'status'=>$form['status'],
								'scheme'=>$form['scheme'],
								'filter'=>1
							)
						)->execute();
		}	

	}

}
