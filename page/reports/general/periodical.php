<?php

class page_reports_general_periodical extends Page {
	public $title="Periodical Repots";
	function page_index(){
		// parent::init();

		$till_date="";
		
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}
		
		if($_GET['accounts']){
			$this->api->stickyGET("filter");
			$this->api->stickyGET("from_date");
			$this->api->stickyGET("to_date");
			$this->api->stickyGET("dealer");
			$this->api->stickyGET("agent");
			$this->js()->univ()->frameURL('Accounts',$this->api->url('./accounts',array('account_type'=>$_GET['accounts'])))->execute();
		}

		$form=$this->add('Form');
		$dealer_field=$form->addField('autocomplete/Basic','dealer');
		$dealer_field->setModel('Dealer');
		$agent_field=$form->addField('autocomplete/Basic','agent');
		$agent_field->setModel('Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid');
		$grid->add('H3',null,'grid_buttons')->set('Periodical Accounts As On '. date('d-M-Y',strtotime($till_date))); 

		$account_model=$this->add('Model_Account');
		$dealer_join = $account_model->leftJoin('dealers','dealer_id');
		$agent_join = $account_model->leftJoin('agents','agent_id');
		$scheme_join = $account_model->join('schemes','scheme_id');

		if($_GET['filter']){
			$this->api->stickyGET("filter");

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$account_model->addCondition('created_at','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$account_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

			if($_GET['dealer']){
				$this->api->stickyGET("dealer");
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			if($_GET['agent']){
				$this->api->stickyGET("agent");
				$account_model->addCondition('agent_id',$_GET['agent']);
			}

		}

		$account_model->add('Controller_Acl');

		$account_model->addCondition('DefaultAC',false);
		$account_model->addCondition('SchemeType',explode(',',ACCOUNT_TYPES));
		
		$account_model->_dsql()->group('account_type');
		$account_model->_dsql()->del('fields');
		$account_model->_dsql()->field('account_type');
		$account_model->_dsql()->field($account_model->dsql()->expr('account_type id'));
		$account_model->_dsql()->field('count(*) count');
		$account_model->_dsql()->field($account_model->dsql()->expr(
									'SUM(IF(account_type ="Saving" OR account_type="Current",'.$scheme_join->table_alias.'.MinLimit,Amount)) amount'
									));



		$grid->setSource($account_model->_dsql());

		$grid->addColumn('text','account_type');
		$grid->addColumn('text','count');
		$grid->addColumn('text','amount');
		$grid->addColumn('Button','accounts');

		$grid->addPaginator(50);

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);


		if($form->isSubmitted()){
			$grid->js()->reload(array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

	function page_accounts(){

		$this->api->stickyGET('account_type');
		$this->api->stickyGET("from_date");
		$this->api->stickyGET("to_date");
		$this->api->stickyGET("dealer");
		$this->api->stickyGET("agent");
	
		$account_model=$this->add('Model_Account');

		if($_GET['from_date']){
			$this->api->stickyGET("from_date");
			$account_model->addCondition('created_at','>',$_GET['from_date']);
		}

		if($_GET['to_date']){
			$this->api->stickyGET("to_date");
			$account_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
		}

		if($_GET['dealer']){
			$this->api->stickyGET("dealer");
			$account_model->addCondition('dealer_id',$_GET['dealer']);
		}

		if($_GET['agent']){
			$this->api->stickyGET("agent");
			$account_model->addCondition('agent_id',$_GET['agent']);
		}

		$account_model->add('Controller_Acl');
		$account_model->addCondition('account_type',$_GET['account_type']);

		$grid=$this->add('Grid');
		$grid->setModel($account_model,array('AccountNumber','created_at','Amount','scheme','member','dealer','agent'));
		$grid->addPaginator(50);
		
	}
}
