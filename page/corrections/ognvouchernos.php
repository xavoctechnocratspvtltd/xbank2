<?php


class page_corrections_ognvouchernos extends Page {
	
	public $title = "Audit Page";

	function init(){
		parent::init();

		if(!$this->app->auth->model->isSuper()){
			$this->add('View_Error')->set('Not permitted');
			return;
		}

		$branch_id  =$this->app->stickyGET('branch');
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull()->set($from_date);
		$form->addField('DatePicker','to_date')->validateNotNull()->set($to_date);
		$branch_field = $form->addField('DropDown','branch')->setEmptyText('Select Branch');
		$branch_field->validateNotNull()->set($branch_id);
		$branch_field->setModel('Branch');

		$form->addSubmit('Go');

		$row_vp = $this->add('VirtualPage');
		$row_vp->set([$this,'editRow']);

		$f_year = $this->api->getFinancialYear($this->app->now);	
		$start_date = $f_year['start_date'];
		$end_date = $f_year['end_date'];

		if($_GET['branch']){

			$this->add('View_Error')->set('BUG TRANSACTIONS');

			$model = $this->add('Model_Transaction');
			$model->addCondition('branch_id',$branch_id);
			$model->addCondition('created_at','>=',$from_date);
			$model->addCondition($model->dsql()->expr('[0]<>[1]',[$model->getElement('cr_sum'),$model->getElement('dr_sum')]));
			$model->setOrder('created_at,id');
			$grid = $this->add('Grid');
			$grid->setModel($model);

			// actual transactions
			$this->add('View')->setHtML('&nbsp;');
			$this->add('View_Info')->set('ALL TRANSACTIONS');
			
			$model = $this->add('Model_Transaction');
			// $model->addCondition([
			// 		[$model->dsql()->expr('[0]<>[1]',[$model->getElement('cr_sum'),$model->getElement('dr_sum')])],
			// 		[$model->dsql()->expr('([0] = 0 AND [1] = 0)',[$model->getElement('cr_sum'),$model->getElement('dr_sum')])]
			// 	]);
			$model->addCondition('branch_id',$branch_id);
			$model->addCondition('created_at','>=',$from_date);
			$model->setOrder('created_at,id');

			$crud = $this->add('CRUD',['allow_add'=>false,'allow_del'=>false,'grid_class'=>'Grid_AccountsBase']);
			$crud->setModel($model,['created_at'],$model->getActualFields());

			// $grid->addFormatter('created_at','grid/inline');
			$crud->grid->addPaginator(1000);
			$crud->grid->addSno();
			$crud->grid->add('VirtualPage')
				->addColumn('edit_transactions')
				->set([$this,'editRow']);
		}

		if($form->isSubmitted()){
			$this->js()->reload($form->get())->execute();
		}
	}

	function editRow($page){
		$id = $_GET[$page->short_name.'_id'];
		$tr_model = $this->add('Model_Transaction');
		$tr_model->load($id);


		$m = $this->add('Model_TransactionRow');
		$m->addCondition('transaction_id',$id);

		$m->addHook('beforeSave',function($m){
			if($m['amountDr'] == $m['amountCr']){
				throw $m->exception('Amount must only be one of Dr/CR filled','ValidityCheck')->setField('amountDr');
			}

			if($m['amountDr'] == 0 && $m['amountCr']==0){
				throw $m->exception('Amount in either DR or CR must be greater then zero','ValidityCheck')->setField('amountDr');
			}

			if($m['amountDr']) $m['side']='DR';
			if($m['amountCr']) $m['side']='CR';

			$t_account= $m->ref('account_id');
			$m['scheme_id'] = $t_account['scheme_id'];
			$m['balance_sheet_id'] = $m->ref('scheme_id')->get('balance_sheet_id');

		});

		// $m->addCondition('reference_id',$tr_model['reference_id']);
		// $m->addCondition('branch_id',$tr_model['branch_id']);
		// $m->addCondition('transaction_type_id',$tr_model['transaction_type_id']);
		// $m->addCondition('Narration',$tr_model['Narration']);
		// $m->addCondition('voucher_no',$tr_model['voucher_no']);

		$crud = $page->add('CRUD');
		$crud->setModel($m,['account_id','amountDr','amountCr'],['account','amountDr','amountCr']);
	}
}