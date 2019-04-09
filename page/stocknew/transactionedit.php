<?php

class page_stocknew_transactionedit extends Page {
	public $title = "Top level transaction Edit";

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$transaction_type = $this->app->stickyGET('transaction_type');
		$from_branch = $this->app->stickyGET('from_branch');
		$to_branch = $this->app->stickyGET('to_branch');
		$item = $this->app->stickyGET('item');

		$this->add('View')->setElement('h3')->set('Filter Form');

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addField('DropDown','transaction_type')->setEmptyText('Please Select ...')->setModel('StockNew_TransactionTemplate');
		$form->addField('DropDown','from_branch')->setEmptyText('Please Select ...')->setModel('Branch');
		$form->addField('DropDown','to_branch')->setEmptyText('Please Select ...')->setModel('Branch');
		$form->addField('autocomplete/Basic','item')->setModel('Model_StockNew_Item');

		$form->addSubmit('Filter');
			
		$model = $this->add('Model_StockNew_Transaction');
		if($from_date){
			$model->addCondition('created_at','>=',$from_date);
		} 
		if($to_date){
			$model->addCondition('created_at','<',$this->app->nextDate($to_date));
		} 
		if($transaction_type) $model->addCondition('transaction_template_type_id',$transaction_type);
		if($from_branch) $model->addCondition('from_branch_id',$from_branch);
		if($to_branch) $model->addCondition('to_branch_id',$to_branch);

		if($item) $model->addCondition('item_id',$item);

		$crud  = $this->add('CRUD');
		$crud->setModel($model);

		$crud->grid->addPaginator(100);
		$crud->add('Controller_Acl',['default_view'=>false]);

		if($form->isSubmitted()){
			$crud->js(null)->reload([
				'from_date'=>$form['from_date']?$form['from_date']:0,
				'to_date'=>$form['to_date']?$form['to_date']:0,
				'transaction_type'=>$form['transaction_type']?$form['transaction_type']:0,
				'from_branch'=>$form['from_branch']?$form['from_branch']:0,
				'to_branch'=>$form['to_branch']?$form['to_branch']:0,
				'item'=>$form['item']?$form['item']:0,
			])->execute();
		}

	}
}