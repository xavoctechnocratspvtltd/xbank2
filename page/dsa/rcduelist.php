<?php

class page_dsa_rcduelist extends Page {
	
	function init(){
		parent::init();

		$as_on_date = $this->api->today;
		if($_GET['as_on_date'])
			$as_on_date = $_GET['as_on_date'];

		$active_dealer_model=$this->add('Model_ActiveDealer');
		$active_dealer_model->addCondition('dsa_id',$this->api->auth->model->id);

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->setEmptyText('Please Select');
		$dealer_field->setModel($active_dealer_model);

		$form->addField('DatePicker','as_on_date');
		$form->addField('dropdown','loan_type')->setValueList(array('vl'=>'VL','fvl'=>'FVL','other'=>'Other'));
		$form->addField('dropdown','status')->setValueList(array('1'=>'Active','0'=>'De Activated'));
		// $form->addField('DropDown','document')->setModel($this->add('Model_Document')->loanDocuments());

		$form->addSubmit('GET List');

		// S.NO.	ACCOUNT NO.	SCHEME	MEMBER NAME	FATHER NAME	CURRENT ADDRESS	PHONE NO.	MATURITY DATE	DEALER NAME
		$accounts_model = $this->add('Model_Account_Loan');
		$q= $accounts_model->dsql();

		$doc_submitted_j = $accounts_model->leftJoin('documents_submitted.accounts_id');
		$doc_submitted_j->addField('documents_id');
		$doc_submitted_j->addField('submitted_on');
		$doc_submitted_j->addField('Description');

		$grid = $this->add('Grid_AccountsBase');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('as_on_date');
			$this->api->stickyGET('loan_type');
			$this->api->stickyGET('status');
			$this->api->stickyGET('document');

			if($_GET['dealer']){
				$this->api->stickyGET('dealer');
				$accounts_model->addCondition('dealer_id',$_GET['dealer']);
			}

			switch ($_GET['loan_type']) {
				case 'vl':
					$accounts_model->addCondition('AccountNumber','like','%vl%');
					break;
				case 'fvl':
					$accounts_model->addCondition('AccountNumber','like','%fvl%');
					break;
				case 'other':
					$accounts_model->addCondition('AccountNumber','not like','%vl%');
					$accounts_model->addCondition('AccountNumber','not like','%fvl%');
					// $accounts_model->_dsql()->where('(accounts.AccountNumber not like "%pl%" and accounts.AccountNumber not like "%pl%")');
					break;
			}

			$accounts_model->addCondition('ActiveStatus',$_GET['status']);

			// if($_GET['document']){
				// $this->api->stickyGET('document');
			
				//HardCoded for Loan
				$vehical_document = $this->add('Model_Document')->loadDocument('VEHICLE NO.');
				$accounts_model->addCondition('documents_id',$vehical_document->id);
				// $accounts_model->addCondition( $q->orExpr()->where('documents_id',$vehical_document->id)->where('documents_id','<>',$vehical_document->id));
				$accounts_model->addCondition($q->orExpr()->where('Description',"")->where('Description is null'));
			// }
			// $accounts_model->addCondition($q->orExpr()->where('documents_id',$_GET['document'])->where('documents_id is null'));
			$accounts_model->addCondition('created_at','<',$this->api->nextDate($as_on_date));
			// $accounts_model->addCondition($q->orExpr()->where('submitted_on','>=',$this->api->nextDate($as_on_date))->where('submitted_on is null'));

		}else{
			$accounts_model->addCondition('id',-1);
		}

		$accounts_model->addExpression('father_name')->set($accounts_model->refSQL('member_id')->fieldQuery('FatherName'));
		$accounts_model->addExpression('address')->set($accounts_model->refSQL('member_id')->fieldQuery('CurrentAddress'));
		$accounts_model->addExpression('phone_nos')->set($accounts_model->refSQL('member_id')->fieldQuery('PhoneNos'));

		$accounts_model->addCondition('DefaultAC',false);
		// $accounts_model->add('Controller_Acl');

		$accounts_model->setOrder('Description','asc');
		$grid->setModel($accounts_model,array('AccountNumber','scheme','member','father_name','address','phone_nos','maturity_date','dealer','Description','submitted_on'));

		$grid->addPaginator(50);
		$grid->addSno();
		$grid->addFormatter('scheme','Wrap');
		$grid->addFormatter('member','Wrap');
		$grid->addFormatter('address','Wrap');
		$grid->addFormatter('Description','Wrap');

		if($form->isSubmitted()){
			$grid->js()->reload(
					array(
						'filter'=>1,
						'as_on_date'=>$form['as_on_date']?:0,
						'loan_type'=>$form['loan_type'],
						'status'=>$form['status'],
						'document'=>$form['document'],
						'dealer'=>$form['dealer'],
						)
				)->execute();
		}
	}
}