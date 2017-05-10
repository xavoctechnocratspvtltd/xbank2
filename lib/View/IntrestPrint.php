<?php

class View_IntrestPrint extends CompleteLister{
	function init(){
		parent::init();

		//Loading CSS File
		$css=array(
			'templates/css/epan.css',
			'templates/css/compact.css'
		);
		
		foreach ($css as $css_file) {
			$link = $this->add('View')->setElement('link');
			$link->setAttr('rel',"stylesheet");
			$link->setAttr('type',"text/css");
			$link->setAttr('href',$css_file);
		}


		//Receiving Paramater
		$member_id= $this->api->stickyGET('member_id');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');


		//Setting UP Required Model Fields 
		$transaction_row=$this->add('Model_TransactionRow');
		//Account Join
		$account_j= $transaction_row->join('accounts','account_id');
		$account_j->addField('member_id');
		$account_j->addField('AccountNumber');
		$account_j->addField('account_type');
		//member join
		$member_j= $account_j->join('members','member_id');
		$member_j->addField('CurrentAddress');
		$member_j->addField('name');
		$member_j->addField('PhoneNos');
		// $member_id->addField('id','selected_member_id')
		//Transaction Join
		$transaction_j = $transaction_row->join('transactions','transaction_id');
		$transaction_type_j = $transaction_j->join('transaction_types','transaction_type_id');
		$transaction_type_j->addField('transaction_type_name','name');

		$transaction_row->addCondition('member_id',$member_id);
		$transaction_row->addCondition(
							$transaction_row->dsql()->orExpr()
								->where(
									$transaction_row->getElement('transaction_type_name'),
										'in',
										[
											TRA_INTEREST_POSTING_IN_SAVINGS,
											TRA_INTEREST_POSTING_IN_FIXED_ACCOUNT,
											// TRA_INTEREST_POSTING_IN_MIS_ACCOUNT,
											TRA_INTEREST_POSTING_IN_HID_ACCOUNT,
											// TRA_INTEREST_POSTING_IN_CC_ACCOUNT,
											TRA_INTEREST_POSTING_IN_RECURRING,
											TRA_INTEREST_POSTING_IN_DDS,
											// TRA_INTEREST_POSTING_IN_LOAN
										]
								)
								->where(
										$transaction_row->dsql()->andExpr()
											->where($transaction_row->getElement('transaction_type_name'), TRA_INTEREST_POSTING_IN_MIS_ACCOUNT)
											->where($transaction_row->getElement('account_type'),'not in',['Saving','Current'])
									)
										
							);

		// not include transaction where sb account is in credit

		// $transaction_row->addCondition(['account_type','not in',['Saving','Current']]);
		// $transaction_row->addCondition('amountCr','<>',0);

		if($_GET['from_date']){
			$transaction_row->addCondition('created_at','>',$_GET['from_date']);
		}

		if($_GET['to_date']){
			$transaction_row->addCondition('created_at','<=',$this->api->nextDate($_GET['to_date']));
		}

		$transaction_row->addCondition('amountCr','>',0);

		// throw new Exception('member_id'.$_GET['member_id']."from_date".$_GET['from_date']."to_date".$_GET['to_date'], 1);
		// throw new \Exception("MEmber_id=".$transaction_row['member_id']."Member_name=".$transaction_row['name']);
		$this->setModel($transaction_row);

		$this->addTotals(array('amountCr'));
	}

	function setModel($model){
		parent::setModel($model);
		
		$member = $this->add('Model_Member')->load($this->model['member_id']);

		$this->template->set('date',date('d-m-Y'));
		$this->template->set('from_date',date('d-m-Y',strtotime($_GET['from_date'])));
		$this->template->set('to_date',date('d-m-Y',strtotime($_GET['to_date'])));
		$this->template->set('customer_id',$this->model['member_id']);
		$this->template->set('name',$member['name']);
		$this->template->set('address',$member['CurrentAddress']);
		$this->template->set('phone_no',$member['PhoneNos']);
		$this->template->set('email_id','');
		$this->template->set('total_intt_paid',$this->model->sum('amountCr'));
		$this->template->set('total_intt_collect','');
	}
	function formatRow(){

		$this->current_row['account_no'] = $this->model['AccountNumber'];
		$this->current_row['currency'] = "INR";
		$this->current_row['intt_paid'] = $this->model['amountCr'];
		$this->current_row['intt_collect'] = 0;
		parent::formatRow();
	}

	function defaultTemplate(){
		return array('view/intrestcertificate');
	}

}