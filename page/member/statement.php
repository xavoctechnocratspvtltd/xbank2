<?php
class page_member_statement extends Page {
	public $title = "Member Account Statement";

	function init(){
		parent::init();
		
		// $this->add('Controller_Acl');
		$account_model=$this->add('Model_Account');
		$account_model->addCondition('member_id',$this->api->auth->model->id);

		$form=$this->add('Form')->addClass('noneprintalbe');
		$account_field = $form->addField('autocomplete/Basic','account')->validateNotNull();
		$account_field->setModel($account_model);

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('Get Statement');

		$v = $this->add('View')->addStyle('width','100%');
		$grid = $v->add('Grid_AccountStatement');
		$transactions = $this->add('Model_TransactionRow');

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
		$grid->setModel($transactions,array('voucher_no','created_at','Narration','amountDr','amountCr'));
		// $grid->addPaginator(10);

		$grid->addSno();

		$grid->addTotals(array('amountCr','amountDr'));
		$grid->addFormatter('Narration','smallWrap');
		// $grid->addFormatter('voucher_no','smallWrap');
		// $grid->addFormatter('voucher_no','smallWrap');

		if($form->isSubmitted()){
			
			$a=$this->add('Model_Account');
			$grid->js()->reload(
					array(
						'account_id'=>$form['account'],
						'from_date'=>($form['from_date'])?:0,
						'to_date'=>($form['to_date'])?:0,
						)
					)->execute();
			$a->tryLoad($form['account']);
			$open = $a->getOpeningBalance();
			$form->displayError('accounts',($open['DR'] - $open['CR']));
		}

	}
}