<?php

class page_reports_general_schemeaccount extends Page {
	public $title="Scheme Account Report";
	
	function init(){
		parent::init();

		$this->account_vp = $this->add('VirtualPage');
		$this->account_vp->set([$this,'showAccounts']);

		$filter = $this->api->stickyGET('filter');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$selected_scheme = $this->api->stickyGET('scheme');
		$this->account_status = $this->api->stickyGET('account_status');
		$status = $this->api->stickyGET('scheme_status');


	}

	function page_index(){

		$filter = $this->api->stickyGET('filter');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$selected_scheme = $this->api->stickyGET('scheme');
		$this->account_status = $this->api->stickyGET('account_status');
		$status = $this->api->stickyGET('scheme_status');


		$scheme_m = $this->add('Model_Scheme');

		// if($_GET['acc_type_scheme']){
		// 	// $scheme_m->addCondition('SchemeType',$_GET['acc_type_scheme']);
		// }

		$form=$this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','scheme_status')->setValueList(array('all'=>'All','0'=>'InActive','1'=>'Active'));
		$form->addField('dropdown','account_status')->setValueList(array('all'=>'All','0'=>'InActive','1'=>'Active'));
		$account_type=$form->addField('DropDown','account_type');
		$array_value = $array_key = explode(',', ACCOUNT_TYPES);
		$account_type->setValueList(array_combine($array_key, $array_value))->setEmptyText('Select Account type');
		$scheme_field=$form->addField('autocomplete/Basic','scheme');
		$scheme_field->setModel($scheme_m);
		$scheme_field->send_other_fields = array($form->getElement('account_type'),$form->getElement('scheme_status'));
		if($scheme_selected = $_GET['o_'.$form->getElement('account_type')->name]){
			$scheme_field->model->addCondition('SchemeType',$scheme_selected);
		}


		if(($status_selected =$_GET['o_'.$form->getElement('scheme_status')->name])!=="all" ){
			$scheme_field->model->addCondition('ActiveStatus',$status_selected==0?false:true);

		}
		
		$form->addSubmit('GET List');

		$grid=$this->add('Grid',array('from_date'=>$from_date,'to_date'=>$to_date));

		$grid->add('H3',null,'grid_buttons')->set('Scheme Wise Account Report From Date '. date('d-M-Y',strtotime($from_date)).'To Date '.date('d-M-Y',strtotime($to_date)) ); 
		
		$scheme = $this->add('Model_Scheme');

		$scheme->addExpression('account_count')->set(function($m,$q){
			$sum_acc = $m->add('Model_Account')
					->addCondition('scheme_id',$q->getField('id'))
					->addCondition('DefaultAC',false)
					;
			if($_GET['account_status'] !=='all'){
				$sum_acc->addCondition('ActiveStatus',$_GET['status']==0?false:true);
			}
			if($_GET['from_date']){
				$sum_acc->addCondition('created_at','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$sum_acc->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}		
					
			return $sum_acc->count();
		});
		$scheme->addExpression('sum_amount')->set(function($m,$q){
			$sum_acc = $m->add('Model_Account')
					->addCondition('scheme_id',$q->getField('id'))
					->addCondition('DefaultAC',false)
					;
			if($_GET['account_status'] !=='all'){
				$sum_acc->addCondition('ActiveStatus',$_GET['status']==0?false:true);
			}		

			if($_GET['from_date']){
				$sum_acc->addCondition('created_at','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$sum_acc->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}
					
			return $sum_acc->sum('Amount');		
		});
		

		if($_GET['filter']){
			if($_GET['account_type']){
				$selected_account_type = $this->api->stickyGET('account_type');
				$scheme->addCondition('SchemeType',$selected_account_type);
			}
			if($_GET['scheme_status'] !=='all'){
				$scheme->addCondition('ActiveStatus',$_GET['scheme_status']==0?false:true);
			}
			if($selected_scheme){
				$scheme->addCondition('id',$selected_scheme);
			}
			// if($_GET['from_date']){
			// 	$scheme->addCondition('created_at','>',$_GET['from_date']);
			// }

			// if($_GET['to_date']){
			// 	$scheme->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			// }
		}else{
				$scheme->addCondition('id',-1);
		}
		$scheme->addCondition('account_count','>',0);

		// $scheme->add('Controller_Acl');
		$grid->setModel($scheme,array('name','account_count','sum_amount'));
		
		$grid->setFormatter('account_count','template')->setTemplate('<a class="account" href="#" data-scheme_id="{$id}">{$account_count}</a>');
		$grid->js('click')->_selector('.account')->univ()->frameURL('Accounts Details',[$this->account_vp->getURL(),'scheme_id'=>$this->js()->_selectorThis()->data('scheme_id')]);

		if($form->isSubmitted()){
			$grid->js()->reload(
						array(
								'to_date'=>$form['to_date']?:0,
								'from_date'=>$form['from_date']?:0,
								'account_type'=>$form['account_type'],
								'scheme_status'=>$form['scheme_status'],
								'account_status'=>$form['account_status'],
								'scheme'=>$form['scheme'],
								'filter'=>1
							)
						)->execute();
		}	

	}

	function showAccounts($page){
		$grid = $page->add('Grid');

		$accounts = $page->add('Model_Account');
		$accounts->addCondition('created_at','>=',$_GET['from_date']);
		$accounts->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));
		$accounts->addCondition('scheme_id',$_GET['scheme_id']);
		$accounts->addCondition('DefaultAC',false);

		$grid->setModel($accounts,['AccountNumber','member_name_only','Amount','created_at','agent']);

	}

}
