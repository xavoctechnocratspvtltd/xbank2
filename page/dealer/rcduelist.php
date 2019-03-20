<?php

class page_dealer_rcduelist extends page_dealer_dashboard{
	function init(){
		parent::init();

		$as_on_date = $this->api->today;
		if($_GET['as_on_date'])
			$as_on_date = $_GET['as_on_date'];

		$tab = $this->add('Tabs');
		$tab->addTab('RC Due List');

		$form=$this->add('Form');
		$dealer_field=$form->addField('dropdown','dealer')->validateNotNull();
		$dealer_field->setModel('ActiveDealer')->addCondition('id',$this->app->auth->model->id);
		$dealer_field->set($this->app->auth->model->id);

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('dropdown','loan_type')->setValueList(array('vl'=>'VL','fvl'=>'FVL','other'=>'Other'));
		$form->addField('dropdown','status')->setValueList(array('1'=>'Active','0'=>'De Activated'));

		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}
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

		$grid_column_array = array('AccountNumber','created_at','scheme','member','father_name','address','phone_nos','maturity_date','dealer','Description','submitted_on');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
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
			if($_GET['from_date'])
				$accounts_model->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$accounts_model->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			// $accounts_model->addCondition($q->orExpr()->where('submitted_on','>=',$this->api->nextDate($as_on_date))->where('submitted_on is null'));


			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$accounts_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
					if(!isset($gridOrder)) $gridOrder = $grid->addOrder();
					$gridOrder->move($this->api->normalizeName($document['name']),'last');
				}
			}

		}else{
			$accounts_model->addCondition('id',-1);
		}

		$accounts_model->addExpression('father_name')->set($accounts_model->refSQL('member_id')->fieldQuery('FatherName'));
		$accounts_model->addExpression('address')->set($accounts_model->refSQL('member_id')->fieldQuery('CurrentAddress'));
		$accounts_model->addExpression('phone_nos')->set($accounts_model->refSQL('member_id')->fieldQuery('PhoneNos'));

		$accounts_model->addCondition('DefaultAC',false);

		$accounts_model->setOrder('Description','asc');
		$grid->setModel($accounts_model, $grid_column_array);

		$grid->addPaginator(500);
		$grid->addSno();
		$grid->addFormatter('scheme','Wrap');
		$grid->addFormatter('member','Wrap');
		$grid->addFormatter('address','Wrap');
		$grid->addFormatter('Description','Wrap');

		if($form->isSubmitted()){

			$send = array(
						'filter'=>1,
						'from_date'=>$form['from_date']?:0,
						'to_date'=>$form['to_date']?:0,
						'loan_type'=>$form['loan_type'],
						'status'=>$form['status'],
						'document'=>$form['document'],
						'dealer'=>$form['dealer'],
						);

			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}

			$grid->js()->reload($send)->execute();
		}
		
	}
}