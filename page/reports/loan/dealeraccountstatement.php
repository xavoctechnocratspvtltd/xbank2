<?php

class page_reports_loan_dealeraccountstatement extends Page {
	
	public $title="Dealer Account Statement Repots";

	function init(){
		parent::init();	

		// echo $_GET['from_date'].'<br>';
		// echo $_GET['to_date'].'<br>';
		// echo $_GET['AccountNumber'].'<br>';
		// echo $_GET['branch_id'].'<br>';
		// return;
		$this->add('Controller_Acl');

		$form=$this->add('Form')->addClass('noneprintalbe');
		$account_field = $form->addField('autocomplete/Basic','account')->validateNotNull();
		$account_field->setModel('Account');


		$form->addField('DatePicker','from_date')->set($_GET['from_date']);
		$form->addField('DatePicker','to_date')->set($_GET['to_date']);
		$form->addSubmit('Get Statement');
		$v = $this->add('View')->addStyle('width','100%');

		if($form->isSubmitted()){
			
			$v->js()->reload(
					array(
						'account_id'=>$form['account'],
						'from_date'=>($form['from_date'])?:0,
						'to_date'=>($form['to_date'])?:0,
						)
					)->execute();
			$a=$this->add('Model_Account');
			$a->tryLoad($form['account']);
			$open = $a->getOpeningBalance();
			$form->displayError('accounts',($open['DR'] - $open['CR']));
		}

		if(!$_GET['account_id'] && !$_GET['AccountNumber']){
			$this->add('View')->set('Please select Account');
			return;
		}

		$grid = $v->add('Grid_AccountStatement');
		
		$joint_memebrs = "";
		if($_GET['account_id']){
			$title_model=$this->add('Model_Account')->addCondition('id',$_GET['account_id']);
			$title_model->tryLoadAny();
			$title_model->getElement('created_at')->type('date');
			$title_acc_name=$title_model->get('name');
			$joint_memebrs = [];
			if($title_model['ModeOfOperation']=='Joint'){
				$joint_model = $title_model->ref('JointMember');
				foreach ($joint_model as $jm) {
					$joint_memebrs[] = $jm['member'];
				}
			}
			$joint_memebrs=implode(", ", $joint_memebrs);
		}
		elseif($_GET['AccountNumber']){
			$title_model=$this->add('Model_Account')->addCondition('AccountNumber',$_GET['AccountNumber']);
			$title_model->tryLoadAny();
			$title_model->getElement('created_at')->type('date');
			$title_acc_name=$title_model->get('name');
		}

		$account_field->set($title_model->id);


		$t_from_date="";
		if($_GET['from_date']){
			$t_from_date=$_GET['from_date'];
		}else{
			$t_from_date=date('Y-m-d',strtotime($title_model['created_at']));
		}
		$grid->add('View',null,'grid_buttons')->setHtml('<div style="text-align:center;font-size:20px">'.$title_acc_name.' <br><small>'. $joint_memebrs .'</small> <br/> <small >From Date - '.$t_from_date." - " . "   To Date - ".($_GET['to_date']?:$this->api->today."</small></div>"));
		$transactions = $this->add('Model_TransactionRow');

		$transactions->addExpression('member')->set(function($m,$q){
			$oth_transaction = $this->add('Model_TransactionRow',['table_alias'=>'md_for_other_account']);
			$oth_transaction->addCondition('transaction_id',$m->getElement('transaction_id'));

			return $oth_transaction->addCondition('amountDr','>',0)->setLimit(1)->fieldQuery('account');
		});

		if($_GET['account_id'] or $_GET['AccountNumber']){
			$this->api->stickyGET('account_id');
			$this->api->stickyGET('AccountNumber');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			if($_GET['account_id']){
				$transactions->addCondition('account_id',$_GET['account_id']);
			}
			if($_GET['AccountNumber']){
				$transactions->join('accounts','account_id')->addField('AccountNumber');
				$transactions->addCondition('AccountNumber',$_GET['AccountNumber']);
			}

			if($_GET['from_date'])
				$transactions->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$transactions->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			if($_GET['account_id']){
				$opening_balance = $this->add('Model_Account')->load($_GET['account_id'])->getOpeningBalance($_GET['from_date']);
			}

			if($_GET['AccountNumber']){
				$opening_balance = $this->add('Model_Account')->loadBy('AccountNumber',$_GET['AccountNumber'])->getOpeningBalance($_GET['from_date']);
			}

			if(($opening_balance['DR'] - $opening_balance['CR']) > 0){
				$opening_column = 'amountDr';
				$opening_amount = $opening_balance['DR'] - $opening_balance['CR'];
				$opening_narration = "To Opening balace";
				$opening_side = 'DR';
			}else{
				$opening_column = 'amountCr';
				$opening_amount = $opening_balance['CR'] - $opening_balance['DR'];
				$opening_narration = "By Opening balace";
				$opening_side = 'CR';
			}
			$grid->addOpeningBalance($opening_amount,$opening_column,array('Narration'=>$opening_narration),$opening_side);
			$grid->addCurrentBalanceInEachRow();
		}else{
			$transactions->addCondition('id',-1);
		}

		// $transactions->add('Controller_Acl');
		$transactions->setOrder('created_at');
		$grid->setModel($transactions,array('voucher_no','created_at','Narration','member','amountDr','amountCr'));
		// $grid->addPaginator(10);

		$grid->addSno();

		$grid->addTotals(array('amountCr','amountDr'));
		$grid->addFormatter('Narration','smallWrap');
		// $grid->addFormatter('voucher_no','smallWrap');
		// $grid->addFormatter('voucher_no','smallWrap');

	}

}
