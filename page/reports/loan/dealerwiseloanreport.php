<?php

class page_reports_loan_dealerwiseloanreport extends Page {
	public $title="Dealer Wise  Dispatch Report";
	function init(){
		parent::init();


		$till_date="";
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addField('dropdown','loan_type')->setValueList(array('all'=>'All','vl'=>'VL','pl'=>'PL','fvl'=>'FVL','sl'=>'SL','other'=>'Other'));
		$form->addField('dropdown','dsa')->setEmptyText('All DSA')->setModel('DSA');
		$form->addField('dropdown','active_status')->setEmptyText('All')->setValueList(['active'=>'Active','inactive'=>'InActive']);
		$form->addField('dropdown','maturity_status')->setEmptyText('All')->setValueList(['mature'=>'Matured','running'=>'Running']);

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->addSno(); 
		$grid->add('H3',null,'grid_buttons')->set('Loan Dispatch Report As On '. date('d-M-Y',strtotime($till_date))); 

		$account_model=$this->add('Model_Account_Loan');

		$member_join = $account_model->join('members','member_id');
		$member_join->addField('FatherName');
		$member_join->addField('CurrentAddress');
		$member_join->addField('PhoneNos');
		
		$account_model->addExpression('no_of_emi')->set(function($m,$q){
			return $m->refSQL('Premium')->count();
		});

		$account_model->addExpression('emi')->set(function($m,$q){
			return $m->refSQL('Premium')->setLimit(1)->fieldQuery('Amount');
		});


		$account_model->addExpression('dsa_id')->set(function($m,$q){
			return $m->refSQL('dealer_id')->fieldQuery('dsa_id');
		});

		$account_model->addExpression('maturity_date')->set(function($m,$q){
			return "DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->add('Model_Scheme')->addCondition('id',$q->getField('scheme_id'))->fieldQuery("NumberOfPremiums")->render().") MONTH)";
		});

		if($_GET['filter']){
			$this->api->stickyGET('filter');

			if($_GET['maturity_status']){
				if($_GET['maturity_status']==='mature'){
					$account_model->_dsql()->where($account_model->dsql()->expr('[0] <= "'.$this->app->today.'"',[$account_model->getElement('maturity_date')]));
				}else{
					$account_model->_dsql()->where($account_model->dsql()->expr('[0] > "'.$this->app->today.'"',[$account_model->getElement('maturity_date')]));
				}
			}
			
			if($_GET['dealer']){
				$this->api->stickyGET('dealer');
				$account_model->addCondition('dealer_id',$_GET['dealer']);
			}

			if($_GET['active_status']){
				$account_model->addCondition('ActiveStatus',$_GET['active_status'] === 'active' ? true:false);
			}

			if($_GET['from_date']){
				$this->api->stickyGET('from_date');
				$account_model->addCondition('created_at','>=',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET('to_date');
				$account_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			}

			switch ($this->app->stickyGET('loan_type')) {
				case 'vl':
					$account_model->addCondition('AccountNumber','like','%vl%');
					$account_model->addCondition('AccountNumber','not like','%fvl%');
					break;
				case 'pl':
					$account_model->addCondition('AccountNumber','like','%pl%');
					break;
				case 'fvl':
					$account_model->addCondition('AccountNumber','like','%FVL%');
					break;
				case 'sl':
					$account_model->addCondition('AccountNumber','like','%SL%');
					break;
				case 'other':
					$account_model->addCondition('AccountNumber','not like','%pl%');
					$account_model->addCondition('AccountNumber','not like','%vl%');
					// $account_model->_dsql()->where('(accounts.AccountNumber not like "%pl%" and accounts.AccountNumber not like "%pl%")');
					break;
			}

			if($this->app->stickyGET('dsa')){
				$account_model->addCondition('dsa_id',$_GET['dsa']);
				if(!$_GET['dealer']) $grid_array[] ='dealer';
			}

		}
		else
			$account_model->addCondition('id',-1);

		$account_model->addCondition('DefaultAC',false);

		$account_model->addExpression('file_charge')->set(function($m,$q){
			$s = $m->add('Model_Scheme',array('table_alias'=>'fcs'));
			$s->addCondition('id',$q->getField('scheme_id'));
			// return "'123'";
			return $q->expr("if([0]=1,[1]/100.0*[2],[2])",array($s->fieldQuery('ProcessingFeesinPercent'),$m->getElement('Amount'),$s->fieldQuery('ProcessingFees')));
		});

		$account_model->addExpression('cheque_amount')->set(function($m,$q){
			return $q->expr("[0]-[1]",array($m->getElement('Amount'),$m->getElement('file_charge')));
		});


		$account_model->addExpression('sum_loan_amount')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('Amount')]);
		});

		$account_model->addExpression('sum_file_charge')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('file_charge')]);
		});

		$account_model->addExpression('sum_cheque_amount')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('cheque_amount')]);
		});

		$account_model->addExpression('sum_emi')->set(function($m,$q){
			return $q->expr('SUM([0])',[$m->getElement('emi')]);
		});

		$account_model->addExpression('count_accounts')->set('count(*)');

		$account_model->_dsql()->group($account_model->dsql()->expr('[0]',[$account_model->getElement('dealer_id')]));

		$grid_array = array('dealer','count_accounts','sum_loan_amount','sum_file_charge','sum_cheque_amount','sum_emi');
		$grid->setModel($account_model,$grid_array);
		

		$order=$grid->addOrder();//->move('deposit','before','dr_sum')->now();
		// $order->move('file_charge','after','Amount')->now();
		// $order->move('cheque_amount','after','file_charge')->now();

		$grid->addPaginator(500);

		$grid->addTotals(array('count_accounts','sum_loan_amount','sum_file_charge','sum_cheque_amount','sum_emi'));

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);
		if($form->isSubmitted()){

			$send = array('from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'document'=>$form['document']?:0,'loan_type'=>$form['loan_type'], 'dsa'=>$form['dsa'],'active_status'=>$form['active_status'],'maturity_status'=>$form['maturity_status'] , 'filter'=>1);
			$grid->js()->reload($send)->execute();
		}		


	}
}