<?php


class page_corrections_bsaudit extends Page {
	
	public $title = "Audit Page";

	function page_index(){
		// parent::init();

		// if(!$this->app->auth->model->isSuper()){
		// 	$this->add('View_Error')->set('Not permitted');
		// 	return;
		// }

		$tabs = $this->add('Tabs');
		$trans_tab = $tabs->addTabURL($this->app->url('./tran'),'Transactions CR != DR');
		$trans_tab = $tabs->addTabURL($this->app->url('./trrow'),'Transactions Row Search');
	}

	function page_tran(){
		$filter = $this->app->stickyGET('filter')?:0;
		$from_date = $this->app->stickyGET('from_date')?:0;
		$to_date = $this->app->stickyGET('to_date')?:0;

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->add('misc\Controller_FormAsterisk');
		$form->addSubmit('go');

		$grid = $this->add('Grid');

		$model = $this->add('Model_Transaction');
		if($filter){
			$model->addCondition('created_at','>=',$from_date);
			$model->addCondition('created_at','<',$this->app->nextDate($to_date));
			$model->addCondition('cr_sum','<>',$model->getElement('dr_sum'));
		}else{
			$model->addCondition('id',-1);
		}
		$grid->setModel($model);
		$grid->addPaginator(100);

		if($form->isSubmitted()){
			$grid->js()->reload(['filter'=>1,'from_date'=>$form['from_date'],'to_date'=>$form['to_date']])->execute();
		}

	}

	function page_trrow(){
		$filter = $this->app->stickyGET('filter')?:0;
		$from_date = $this->app->stickyGET('from_date')?:0;
		$to_date = $this->app->stickyGET('to_date')?:0;
		$based_on = $this->app->stickyGET('based_on');
		$cr_amount = $this->app->stickyGET('cr_amount')?:0;
		$dr_amount = $this->app->stickyGET('dr_amount')?:0;

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addField('DropDown','based_on')
			->setValueList([
					'not_having_tr'=>'Not Having Transaction',
					'group_mismatch'=>'Balance, Group Or PandL Group Mismatch',
				])
			->setEmptyText("...");
		$form->addField('cr_amount')->set(0);
		$form->addField('dr_amount')->set(0);
		$form->addsubmit('go');
		$form->add('misc\Controller_FormAsterisk');

		$model = $this->add('Model_TransactionRow');
		if($filter){

			$model->addCondition('created_at','>=',$from_date);
			$model->addCondition('created_at','<',$this->app->nextDate($to_date));

			if($based_on === "not_having_tr"){
				$model->addExpression('has_transaction')->set(function($m,$q){
					return $m->add('Model_Transaction')->addCondition('id',$m->getElement('transaction_id'))->count();
				})->sortable(true);
				$model->addCondition([['has_transaction',0],['has_transaction',null]]);
			}

			if($based_on === "group_mismatch"){
				$model->addExpression('scheme_match')->set(function($m,$q){
					$am = $m->add('Model_Account')->addCondition('id',$m->getElement('account_id'));
					return $q->expr('IF([0]=[1],1,0)',[$am->fieldQuery('scheme_id'),$m->getElement('scheme_id')]);
				});

				$model->addExpression('bs_match')->set(function($m,$q){
					$sm = $m->add('Model_Scheme')->addCondition('id',$m->getElement('scheme_id'));
					return $q->expr('IF([0]=[1],1,0)',[$sm->fieldQuery('balance_sheet_id'),$m->getElement('balance_sheet_id')]);
				});
				
				$model->addCondition([['scheme_match',0],['bs_match',0]]);
			}

			if($dr_amount){
				$model->addCondition('amountDr',$dr_amount);
			}
			if($cr_amount){
				$model->addCondition('amountCr',$cr_amount);
			}

		}else{
			$model->addCondition('id',-1);
		}

		$grid = $this->add('Grid_AccountsBase');
		$grid->setModel($model);
		$grid->addPaginator(100);
		if($form->isSubmitted()){
			$grid->js()->reload([
					'filter'=>1,
					'from_date'=>$form['from_date'],
					'to_date'=>$form['to_date'],
					'cr_amount'=>$form['cr_amount'],
					'dr_amount'=>$form['dr_amount'],
					'based_on'=>$form['based_on'],
				])->execute();
		}

	}

}