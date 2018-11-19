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
		$mo_field = $form->addField('autocomplete/Basic','mo');
		$mo_field->setModel('Mo');

		$team_field = $form->addField('autocomplete/Basic','team');
		$team_field->setModel('Team');

		$new_renew_field = $form->addField('DropDown','new_or_renew')->setEmptyText('All')->setValueList(array_combine(['New','ReNew'],['New','ReNew']));

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		
		$form->addField('account_amount_from');
		$form->addField('account_amount_to');

		if($this->app->currentStaff['AccessLevel']>=80){
			$form->addField('DropDown','branch_id')->setEmptyText('All')->setModel('Branch');
		}

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Periodical Accounts As On '. date('d-M-Y',strtotime($till_date))); 

		$account_model=$this->add('Model_Account');
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

			if($_GET['dealer']){
				$this->api->stickyGET("dealer");
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			if($_GET['agent']){
				$this->api->stickyGET("agent");
				$account_model->addCondition('agent_id',$_GET['agent']);
			}

			if($_GET['mo_id']){
				$this->api->stickyGET('mo_id');
				$account_model->addCondition('mo_id',$_GET['mo_id']);
			}

			if($_GET['team_id']){
				$this->api->stickyGET('team_id');
				$account_model->addCondition('team_id',$_GET['team_id']);
			}

			if($_GET['new_or_renew']){
				$this->api->stickyGET('new_or_renew');
				$account_model->addCondition('new_or_renew',$_GET['new_or_renew']);
			}

			if($this->app->currentStaff['AccessLevel']>=80){
				if($_GET['branch_id']){
					$this->api->stickyGET('branch_id');
					$account_model->addCondition('branch_id',$_GET['branch_id']);
				}
			}

		}

		if($this->app->currentStaff['AccessLevel'] < 80){
			$account_model->add('Controller_Acl');
		}

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

		$grid->addTotals(array('amount'));
	
		$this->add('VirtualPage')
			->addColumn('detail','details',array('icon'=>'plus'),$grid)
			->set(function($p){
				$account_model=$p->add('Model_Account');
				$account_model->addCondition('account_type',$p->id);

				if(!$this->app->currentStaff->isSuper()){
					$account_model->add('Controller_Acl');
				}

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

					if($_GET['dealer']){
						$p->api->stickyGET("dealer");
						$account_model->addCondition('dealer_id',$_GET['dealer']);
					}

					if($_GET['agent']){
						$p->api->stickyGET("agent");
						$account_model->addCondition('agent_id',$_GET['agent']);
					}

					if($_GET['mo_id']){
						$this->api->stickyGET('mo_id');
						$account_model->addCondition('mo_id',$_GET['mo_id']);
					}

					if($_GET['team_id']){
						$this->api->stickyGET('team_id');
						$account_model->addCondition('team_id',$_GET['team_id']);
					}

					if($_GET['new_or_renew']){
						$this->api->stickyGET('new_or_renew');
						$account_model->addCondition('new_or_renew',$_GET['new_or_renew']);
					}

					if($this->app->currentStaff->isSuper()){
						if($_GET['branch_id']){
							$this->api->stickyGET('branch_id');
							$account_model->addCondition('branch_id',$_GET['branch_id']);
						}
					}

				}

				$account_model->addExpression('sm_no')->set(function($m,$q){
					$sm_a = $m->add('Model_Account',array('table_alias'=>'sm_a'));
					$sm_a->addCondition('member_id',$q->getField('member_id'));
					$sm_a->addCondition('AccountNumber','like','SM%');
					$sm_a->setLimit(1);
					return $sm_a->fieldQuery('AccountNumber');
				});

				$account_model->addExpression('pan_no')->set($account_model->refSQL('member_id')->fieldQuery('PanNo'));
				$account_model->addExpression('adhaar_no')->set($account_model->refSQL('member_id')->fieldQuery('AdharNumber'));
				$account_model->addExpression('father_name')->set($account_model->refSQL('member_id')->fieldQuery('FatherName'));
				$account_model->addExpression('address')->set($account_model->refSQL('member_id')->fieldQuery('PermanentAddress'));
				$account_model->addExpression('phone_no')->set($account_model->refSQL('member_id')->fieldQuery('PhoneNos'));
				$account_model->addExpression('agent_saving_acc')->set($account_model->refSQL('agent_id')->fieldQuery('account'));
				$account_model->addExpression('agent_phone_no')->set($account_model->refSQL('agent_id')->fieldQuery('agent_phone_no'));
				$account_model->addExpression('member_no')->set($account_model->refSQL('member_id')->fieldQuery('member_no'))->caption('Member No');
				
				$grid = $p->add('Grid_AccountsBase');
				$grid->addSno();
				$grid->setModel($account_model,array('member_no','sm_no','created_at','AccountNumber','scheme','Amount','pan_no','adhaar_no','member','father_name','address','phone_no','agent','dealer','mo','team','agent_saving_acc','agent_phone_no','Nominee','NomineeAge','RelationWithNominee','repayment_mode'));
				$grid->addFormatter('agent','Wrap');
				$grid->addFormatter('agent_saving_acc','Wrap');
				$grid->addFormatter('team','Wrap');
				$grid->addFormatter('address','Wrap');
				$grid->addFormatter('member','Wrap');
				$grid->addPaginator(500);
				$grid->addTotals(array('Amount'));
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
			$send_array = array('dealer'=>$form['dealer'],'agent'=>$form['agent'],'to_date'=>$form['to_date']?:0,'from_date'=>$form['from_date']?:0,'team_id'=>$form['team'],'mo_id'=>$form['mo'],'new_or_renew'=>$form['new_or_renew'],'filter'=>1);
			if($this->app->currentStaff->isSuper()){
				$send_array['branch_id'] = $form['branch_id'];
			}
			$grid->js()->reload($send_array)->execute();
		}	

	}

	function page_accounts(){

		$this->api->stickyGET('account_type');
		$this->api->stickyGET("from_date");
		$this->api->stickyGET("to_date");
		$this->api->stickyGET("dealer");
		$this->api->stickyGET("agent");
	
		$account_model = $this->add('Model_Account');
		$account_model->addCondition('account_type',$p->id);

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

			if($_GET['dealer']){
				$this->api->stickyGET("dealer");
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			if($_GET['agent']){
				$this->api->stickyGET("agent");
				$account_model->addCondition('agent_id',$_GET['agent']);
			}

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
