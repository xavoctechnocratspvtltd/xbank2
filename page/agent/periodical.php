<?php

class page_agent_periodical extends Page {
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
		// $dealer_field=$form->addField('autocomplete/Basic','dealer');
		// $dealer_field->setModel('Dealer');
		// $agent_field=$form->addField('autocomplete/Basic','agent');
		// $agent_field->setModel('Agent');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		
		$form->addField('account_amount_from');
		$form->addField('account_amount_to');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Periodical Accounts As On '. date('d-M-Y',strtotime($till_date))); 

		$account_model=$this->add('Model_Account');
		$account_model->addCondition('agent_id',$this->api->auth->model->id);
		$dealer_join = $account_model->leftJoin('dealers','dealer_id');
		$agent_join = $account_model->leftJoin('agents','agent_id');
		$scheme_join = $account_model->join('schemes','scheme_id');

		if($_GET['filter']){
			$this->api->stickyGET("filter");

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$account_model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$account_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

			// if($_GET['dealer']){
			// 	$this->api->stickyGET("dealer");
			// 	$account_model->addCondition('dealer_id',$_GET['dealer']);
			// }

			// if($_GET['agent']){
			// 	$this->api->stickyGET("agent");
			// }

		}

		// $account_model->add('Controller_Acl');

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
		$grid->addSno();
		$grid->addColumn('text','account_type');
		$grid->addColumn('text','count');
		$grid->addColumn('text','amount');
		$self=$this;
		$this->add('VirtualPage')
			->addColumn('detail','details',array('icon'=>'plus'),$grid)
			->set(function($p)use($self){
				$account_model=$p->add('Model_Account');
				$account_model->addCondition('agent_id',$self->api->auth->model->id);
				$account_model->addCondition('account_type',$p->id);
				// $account_model->add('Controller_Acl');

				if($_GET['filter']){
					$p->api->stickyGET("filter");

					if($_GET['from_date']){
						$p->api->stickyGET("from_date");
						$account_model->addCondition('created_at','>=',$_GET['from_date']);
					}

					if($_GET['to_date']){
						$p->api->stickyGET("to_date");
						$account_model->addCondition('created_at','<',$p->api->nextDate($_GET['to_date']));
					}

					// if($_GET['dealer']){
					// 	$p->api->stickyGET("dealer");
					// 	$account_model->addCondition('dealer_id',$_GET['dealer']);
					// }

					// if($_GET['agent']){
					// 	$p->api->stickyGET("agent");
					// }

				}

				$account_model->addExpression('sm_no')->set(function($m,$q){
					$sm_a = $m->add('Model_Account',array('table_alias'=>'sm_a'));
					$sm_a->addCondition('member_id',$q->getField('member_id'));
					$sm_a->addCondition('AccountNumber','like','SM%');
					$sm_a->setLimit(1);
					return $sm_a->fieldQuery('AccountNumber');
				});

				$account_model->addExpression('father_name')->set($account_model->refSQL('member_id')->fieldQuery('FatherName'));
				$account_model->addExpression('address')->set($account_model->refSQL('member_id')->fieldQuery('PermanentAddress'));
				$account_model->addExpression('phone_no')->set($account_model->refSQL('member_id')->fieldQuery('PhoneNos'));
				$account_model->addExpression('agent_saving_acc')->set($account_model->refSQL('agent_id')->fieldQuery('account'));
				$account_model->addExpression('agent_phone_no')->set($account_model->refSQL('agent_id')->fieldQuery('agent_phone_no'));

				$grid = $p->add('Grid_AccountsBase');
				$grid->addSno();
				$grid->setModel($account_model,array('member_id','sm_no','created_at','AccountNumber','scheme','Amount','member','father_name','phone_no','dealer','Nominee','NomineeAge','RelationWithNominee'));

				$grid->addPaginator(500);
			});

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);
		
		if($form->isSubmitted()){
			$grid->js()->reload(array(/*'dealer'=>$form['dealer'],'agent'=>$form['agent'],*/'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'filter'=>1))->execute();
		}	

	}

	function page_accounts(){

		$this->api->stickyGET('account_type');
		$this->api->stickyGET("from_date");
		$this->api->stickyGET("to_date");
	
		$account_model = $this->add('Model_Account');
		$account_model->addCondition('account_type',$p->id);
		$account_model->addCondition('agent_id',$this->api->auth->model->id);

		if($_GET['filter']){
			$this->api->stickyGET("filter");

			if($_GET['from_date']){
				$this->api->stickyGET("from_date");
				$account_model->addCondition('created_at','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET("to_date");
				$account_model->addCondition('created_at','<',$p->api->nextDate($_GET['to_date']));
			}

			// if($_GET['dealer']){
			// 	$this->api->stickyGET("dealer");
			// 	$account_model->addCondition('dealer_id',$_GET['dealer']);
			// }

			// if($_GET['agent']){
			// 	$this->api->stickyGET("agent");
			// }

		}

		$account_model->addExpression('sm_no')->set(function($m,$q){
			$sm_account = $m->add('Model_Account',array('table_alias'=>'sm_acc'));
			$sm_account->addCondition('member_id',$q->getField('member_id'));
			$sm_account->addCondition('AccountNumber','like','SM%');
			$sm_account->setLimit(1);
			return $sm_account->fieldQuery('AccountNumber');
		});

		$account_model->addExpression('father_name')->set($account_model->refSQL('member_id')->fieldQuery('FatherName'));
		$account_model->addExpression('permanent_address')->set($account_model->refSQL('member_id')->fieldQuery('PermanentAddress'));
		$account_model->addExpression('agent_saving_account')->set($account_model->refSQL('agent_id')->fieldQuery('account_id'));
		$account_model->addExpression('phone_no')->set($account_model->refSQL('member_id')->fieldQuery('PhoneNos'));
		$account_model->addExpression('agent_phone_no')->set($account_model->refSQL('agent_id')->fieldQuery('agent_phone_no'));

		$grid= $p->add('Grid_AccountsBase');
		$grid->addSno();

		$member_m = $account_model->getElement('member_id')->getModel();
		$member_m->title_field ='name';
		
		$grid->setModel($account_model,array('sm_no','created_at','AccountNumber','scheme','Amount','member','father_name','permanent_address','phone_no','agent','dealer','agent_saving_account','agent_phone_no'));

		$grid->addFormatter('permanent_address','wrap');
		$grid->addQuickSearch(array('AccountNumber','member'));
		$grid->addPaginator(500);
		
	}
}
