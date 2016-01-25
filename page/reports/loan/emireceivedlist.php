<?php

class page_reports_loan_emireceivedlist extends Page {
	public $title="EMI Received List";
	function init(){
		parent::init();
		$till_date="";
		if($_GET['to_date']){
			$till_date=$_GET['to_date'];
		}

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel('ActiveDealer');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addField('dropdown','loan_type')->setValueList(array('all'=>'All','vl'=>'VL','pl'=>'PL','other'=>'Other'));
		$document=$this->add('Model_Document');
		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase');
		$grid->add('H3',null,'grid_buttons')->set('Loan EMI Received List As On '. date('d-M-Y',strtotime($till_date))); 

		$transaction_row_model=$this->add('Model_TransactionRow');
		$transaction_row_model->getElement('amountCr')->caption('Amount');
		$transaction_row_model->getElement('created_at')->caption('Received Date');
		
		$transaction_join = $transaction_row_model->join('transactions','transaction_id');
		$transaction_type_join = $transaction_join->join('transaction_types','transaction_type_id');
		$account_join = $transaction_row_model->join('accounts','account_id');
		$dealer_join = $account_join->leftJoin('dealers','dealer_id');
		$scheme_join = $account_join->join('schemes','scheme_id');
		
		$member_join = $account_join->join('members','member_id');
		// $member_join->addField('member_id','id');
		$member_join->addField('member_name','name');
		$member_join->addField('member_address','CurrentAddress');
		$member_join->addField('member_landmark','landmark');

		$member_join->addField('FatherName');

		$dealer_join->addField('dealer_name','name');
		$account_join->addField('AccountNumber');
		$account_join->addField('dealer_id');
		$scheme_join->addField('SchemeType');
		$transaction_type_join->addField('transaction_type_name','name');

		$transaction_row_model->addCondition('transaction_type_name',TRA_LOAN_ACCOUNT_AMOUNT_DEPOSIT);
		$transaction_row_model->addCondition('amountCr','>',0);
		$transaction_row_model->addCondition('SchemeType','Loan');

		$transaction_row_model->setOrder('account_id desc,created_at desc');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			if($_GET['from_date']){
				$this->api->stickyGET('from_date');
				$transaction_row_model->addCondition('created_at','>',$_GET['from_date']);
			}

			if($_GET['to_date']){
				$this->api->stickyGET('to_date');
				$transaction_row_model->addCondition('created_at','<=',$this->api->nextDate($_GET['to_date']));
			}

			if($_GET['dealer']){
				$this->api->stickyGET('dealer');
				$transaction_row_model->addCondition('dealer_id',$_GET['dealer']); 
			}

			$this->api->stickyGET('loan_type');
			switch ($_GET['loan_type']) {
				case 'vl':
					$transaction_row_model->addCondition('AccountNumber','like','___vl%');
					break;
				
				case 'pl':
					$transaction_row_model->addCondition('AccountNumber','like','___pl%');
					break;
				case 'other':
					$transaction_row_model->addCondition('AccountNumber','not like','%PL%');
					$transaction_row_model->addCondition('AccountNumber','not like','%VL%');
					break;
			}

		}else
			$transaction_row_model->addCondition('id',-1);


		$transaction_row_model->add('Controller_Acl');
		$grid->setModel($transaction_row_model,array('AccountNumber','member_name','member_address','member_landmark','FatherName','amountCr','Narration','created_at','dealer_name'));

		// $grid->addHook('formatRow',function($g){
		// 	// $this->addExpression('member_name')->set('CONCAT(name," [",id, "] :: ",IFNULL(PermanentAddress,""),"::[",IFNUll(landmark,""),"]")')->display(array('grid'=>'shorttext'));			
		// 	$g->current_row_html['member_name'] = $g->model['member_name']."[".$g->model['member_id']."]"." :: " . $g->model['member_address']."  [ " .$g->model['member_landmark' ]."]";
		// });

		// $grid->removeColumn('member_landmark');
		// $grid->removeColumn('member_address');
		// $grid->addFormatter('member_name','wrap');
		$grid->addFormatter('member_address','wrap');
		$grid->addPaginator(500);
		$grid->addSno();
		$grid->addTotals(array('amountCr'));

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);


		if($form->isSubmitted()){

			$grid->js()->reload(array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'loan_type'=>$form['loan_type'],'filter'=>1))->execute();

		}		


	}
}