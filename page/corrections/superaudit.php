<?php


class page_corrections_superaudit extends Page {
	
	public $title = "Audit Page";

	function page_index(){
		// parent::init();

		if(!$this->app->auth->model->isSuper()){
			$this->add('View_Error')->set('Not permitted');
			return;
		}

		$tabs = $this->add('Tabs');

		$trans_tab = $tabs->addTabURL($this->app->url('./transtab'),'Transactions Tab');
		$cr_dr_tab = $tabs->addTabURL($this->app->url('./crdrtab'),'Transaction CR/DR');
		$voucher_no_tab = $tabs->addTabURL($this->app->url('./vouchernumbertab'),'Voucher Number Correction');
		
	}

	function page_vouchernumbertab(){

		ini_set("memory_limit","1000M");

		$tr_m = $this->add('Model_Transaction');
		$tr_m->addCondition('branch_id',5);
		$tr_m->addCondition('created_at','>=','2018-04-01');
		$tr_m->setOrder('voucher_no');

		$btn = $this->add('Button')->set('Update');
		$grid=  $this->add('Grid');
		$grid->setModel($tr_m);
		$grid->addPaginator(50);

		$btn->on('click',function($js,$data)use($grid){
			$tr_m = $this->add('Model_Transaction');
			$tr_m->addCondition('branch_id',5);
			$tr_m->addCondition('created_at','>=','2018-04-01');
			$tr_m->setOrder('voucher_no');
			$i=1;
			foreach ($tr_m as $m) {
				$tr_m['voucher_no'] = $i++;
				$tr_m->save();
			}

			return $js->reload()->_selector($grid);
		});

	}

	function page_crdrtab(){
		$m = $this->app->db->dsql()->expr('select AccountNumber, (select sum(amountDr) from transaction_row tr where tr.account_id = accounts.id ) tr_dr, (select sum(amountCr) from transaction_row tr where tr.account_id = accounts.id ) tr_cr, OpeningBalanceDr, CurrentBalanceDr, OpeningBalanceCr, CurrentBalanceCr from  accounts  HAVING  tr_dr + OpeningBalanceDr <> CurrentBalanceDr OR tr_cr + OpeningBalanceCr <> CurrentBalanceCr limit 0, 1');
		

		$grid = $this->add('Grid');
		$grid->setSource($m);
		$grid->addColumn('AccountNumber');
		$grid->addColumn('tr_dr');
		$grid->addColumn('tr_cr');
		$grid->addColumn('OpeningBalanceDr');
		$grid->addColumn('CurrentBalanceDr');
		$grid->addColumn('OpeningBalanceCr');
		$grid->addColumn('CurrentBalanceCr');

		$grid->add('View',null,'grid_buttons')->set('ACCOUNTS IN SUPER EDIT MODE');

		$grid->addPaginator(10);
		$grid->addQuickSearch(['AccountNumber']);
	}

	function page_transtab(){
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
			$model->addHook('beforeSave',function($tr_model){
				$m = $this->add('Model_TransactionRow');
				$m->addCondition('transaction_id',$tr_model->id);

				foreach ($m as $t) {
					$t['created_at'] = $tr_model['created_at'];
					$t->save();
				}
			});
			// $model->addCondition([
			// 		[$model->dsql()->expr('[0]<>[1]',[$model->getElement('cr_sum'),$model->getElement('dr_sum')])],
			// 		[$model->dsql()->expr('([0] = 0 AND [1] = 0)',[$model->getElement('cr_sum'),$model->getElement('dr_sum')])]
			// 	]);
			$model->addCondition('branch_id',$branch_id);
			$model->addCondition('created_at','>=',$from_date);
			$model->setOrder('created_at,id');

			$crud = $this->add('CRUD',['allow_add'=>false,'allow_del'=>false,'grid_class'=>'Grid_AccountsBase']);
			$crud->setModel($model,['created_at'],['transaction_type','reference','branch','voucher_no','Narration','created_at','cr_sum','dr_sum']);

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

		$ref_model = $this->add('Model_Account')->tryLoad($tr_model['reference_id']);
		$page->add('View')->set($ref_model['name'].' :: '. $ref_model['branch_code'] .' Interest Paid On '. $ref_model['scheme']);

		$m->addHook('beforeSave',function($m)use($tr_model){
			if($m['amountDr'] == $m['amountCr']){
				throw $m->exception('Amount must only be one of Dr/CR filled','ValidityCheck')->setField('amountDr');
			}

			if($m['amountDr'] == 0 && $m['amountCr']==0){
				throw $m->exception('Amount in either DR or CR must be greater then zero','ValidityCheck')->setField('amountDr');
			}

			if($m['amountDr']) $m['side']='DR';
			if($m['amountCr']) $m['side']='CR';

			$m['created_at'] = $tr_model['created_at'];
			$m['accounts_in_side'] = 1;

			$t_account= $m->ref('account_id');
			$m['scheme_id'] = $t_account['scheme_id'];
			$m['balance_sheet_id'] = $m->ref('scheme_id')->get('balance_sheet_id');

		});

		$m->addHook('afterSave',function($m_prev)use($tr_model){

			$ref_model = $this->add('Model_Account')->tryLoad($tr_model['reference_id']);
			
			$m = $this->add('Model_TransactionRow');
			$m->addCondition('transaction_id',$tr_model->id);
			$m->addCondition('account_id',$this->add('Model_Account')->loadBy('AccountNumber',$ref_model['branch_code'] .' Interest Paid On '. $ref_model['scheme'])->get('id'));
			$m->tryLoadAny();

			$m['amountDr']=  $m_prev['amountCr'];
			$m['side']='DR';

			$m['created_at'] = $tr_model['created_at'];
			$m['accounts_in_side'] = 1;

			$t_account= $m->ref('account_id');
			$m['scheme_id'] = $t_account['scheme_id'];
			$m['balance_sheet_id'] = $m->ref('scheme_id')->get('balance_sheet_id');

			$m->save();

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