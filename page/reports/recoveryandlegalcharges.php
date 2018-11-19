<?php

class page_reports_recoveryandlegalcharges extends Page {

	public $title ="Recovery And Legal Reports";

	function init(){
		parent::init();

		$transaction_array = RECOEVRY_ANY_LEGAL_CHARGES_ACCOUNT_TRA_ARRAY;

		$this->app->stickyGET('from_date');
		$this->app->stickyGET('to_date');
		$this->app->stickyGET('type');

		$form = $this->add('Form');
		$form->addField('DropDown','type')->setEmptyText('Please Select')->validateNotNull()->setValueList(array_combine(array_keys($transaction_array), array_keys($transaction_array)));
		$form->addField('DatePicker','from_date')->set($_GET['from_date'])->validateNotNull();
		$form->addField('DatePicker','to_date')->set($_GET['to_date'])->validateNotNull();
		$form->addSubmit('Go ');


		$account = $this->add('Model_Account');
			
		$fields=['AccountNumber'];
		$totals = [];

		if($_GET['from_date']){
			$account->addExpression($_GET['type'])->set(function($m,$q)use($transaction_array){
				$trtype = $this->add('Model_TransactionType')->loadBy('name',$transaction_array[$_GET['type']][1]);
				return $this->add('Model_TransactionRow')
						->addCondition('created_at','>',$_GET['from_date'])
						->addCondition('created_at','<=',$this->app->nextDate($_GET['to_date']))
						->addCondition('transaction_type_id',$trtype->id)
						->addCondition('reference_id',$q->getField('id'))
						->sum('amountDr');
			});
			$totals[] = $fields[] = $_GET['type'];
			$account->addCondition($_GET['type'],'>',0);
		}else{
			$account->addCondition('id',-1);
		}

		$grid = $this->add('Grid_AccountsBase');
		$grid->setModel($account,$fields);
		$grid->addSno();

		if(count($totals)) $grid->addTotals($totals);

		$grid->addPaginator(50);

		if($form->isSubmitted()){
			$vals = [
						'type'=>$form['type'],
						'from_date'=>($form['from_date'])?:0,
						'to_date'=>($form['to_date'])?:0,
					];
			$grid->js()->reload($vals)->execute();
		}

	}
}