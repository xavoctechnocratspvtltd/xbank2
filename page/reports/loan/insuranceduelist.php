<?php
class page_reports_loan_insuranceduelist extends Page {
	public $title="Insurance Due List";
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
		$form->addField('Radio','insurance_tenure')->setValueList(array(' '=>'None','1 Year'=>'1 Year','5 Year'=>'5 Year'));
		$document=$this->add('Model_Document');
		$document->addCondition('LoanAccount',true);
		foreach ($document as $junk) {
			$form->addField('CheckBox','doc_'.$document->id, $document['name']);
		}

		$form->addSubmit('GET List');

		$grid=$this->add('Grid_AccountsBase'); 
		$grid->add('H3',null,'grid_buttons')->set('Loan Insurance Due List As On '. date('d-M-Y',strtotime($till_date))); 

		$accounts_model=$this->add('Model_Active_Account_Loan');
		$member_join=$accounts_model->join('members','member_id');
		// $member_join->addField('member_name','name');
		$member_join->addField('FatherName');
		$member_join->addField('PhoneNos');
		$member_join->addField('PermanentAddress');

		$accounts_model->addExpression('insurance_month')->set('(MONTH(LoanInsurranceDate))');
		$accounts_model->addExpression('insurance_date')->set('(DAY(LoanInsurranceDate))');
		$q = $accounts_model->dsql();

		$grid_column_array = array('AccountNumber','member','FatherName','PermanentAddress','PhoneNos','LoanInsurranceDate','dealer','insurance_tenure','insurance_month','insurance_date','maturity_date');

		if($_GET['filter']){
			$this->api->stickyGET('filter');
			
			if($_GET['from_date']){
				$this->api->stickyGET('from_date');
				$accounts_model->_dsql()->having('insurance_month','>=',(int) date('m',strtotime($_GET['from_date'])));
				$accounts_model->_dsql()->having('insurance_date','>=',(int)date('d',strtotime($_GET['from_date'])));
				$accounts_model->_dsql()->having('maturity_date','>=',$this->api->nextDate($_GET['from_date']));
			}

			if($_GET['to_date']){
				$this->api->stickyGET('to_date');
				$accounts_model->_dsql()->having('insurance_month','<=',(int)date('m',strtotime($_GET['to_date'])));
				$accounts_model->_dsql()->having('insurance_date','<=',(int)date('d',strtotime($_GET['to_date'])));
			}

			if($_GET['insurance_tenure']){
				$this->api->stickyGET('insurance_tenure');
				$accounts_model->addCondition('insurance_tenure',$_GET['insurance_tenure']);
			}
			
			if($_GET['dealer']){
				$this->api->stickyGET('dealer');
				$accounts_model->addCondition('dealer_id',$_GET['dealer']);
			}

			foreach ($document as $junk) {
				$doc_id = $document->id;
				if($_GET['doc_'.$document->id]){
					$this->api->stickyGET('doc_'.$document->id);
					$accounts_model->addExpression($this->api->normalizeName($document['name']))->set(function($m,$q)use($doc_id ){
						return $m->refSQL('DocumentSubmitted')->addCondition('documents_id',$doc_id )->fieldQuery('Description');
					});
					$grid_column_array[] = $this->api->normalizeName($document['name']);
				}
			}

		}else
			$accounts_model->addCondition('id',-1);

		$accounts_model->setOrder('id','desc');
		$accounts_model->addCondition('DefaultAC',false);
		$accounts_model->addCondition('AccountNumber','like','%vl%');
		
		$accounts_model->add('Controller_Acl');

		$accounts_model->getElement('LoanInsurranceDate')->caption('Insurance Due Date');


		$grid->setModel($accounts_model,$grid_column_array);

		$grid->addMethod('format_onlyDateMonth',function($g,$f){
			$g->current_row[$f] = date('d-M',strtotime($g->model[$f]));
		});

		$grid->addFormatter('LoanInsurranceDate','onlyDateMonth');

		$grid->addMethod('format_balance_on_date',function($g,$f){
			$bal = $g->model->getOpeningBalance($_GET['to_date']);
			if($bal['cr'] > $bal['dr'])
				$amt = ($bal['cr'] - $bal['dr']) . ' Cr';
			else
				$amt = ($bal['dr'] - $bal['cr']) . ' Dr';

			$g->current_row[$f] = $amt;

		});

		$grid->addColumn('balance_on_date','amount');
		$grid->addFormatter('member','wrap');

		$grid->addPaginator(50);
		$grid->addSno();
		$grid->removeColumn('insurance_month');
		$grid->removeColumn('insurance_date');

		$js=array(
			$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
			$this->js()->_selector('#header')->toggle(),
			$this->js()->_selector('#footer')->toggle(),
			$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
			$this->js()->_selector('.atk-form')->toggle(),
			);

		$grid->js('click',$js);

		if($form->isSubmitted()){
			$send = array('dealer'=>$form['dealer'],'from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0,'insurance_tenure'=>$form['insurance_tenure']?:'','filter'=>1);
			foreach ($document as $junk) {
				if($form['doc_'.$document->id])
					$send['doc_'.$document->id] = $form['doc_'.$document->id];
			}
			$grid->js()->reload($send)->execute();

		}	

	}
}