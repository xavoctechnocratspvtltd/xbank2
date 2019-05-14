<?php

class page_memorandum extends Page {
	public $title='All Memorandum';

	function init(){
		parent::init();

		$this->add('Controller_Acl',['default_view'=>false]);
		
		$model = $this->add('Model_Memorandum_Transaction');
		$model->addExpression('dr_account_no')->set(function($m,$q){
			$x = $m->add('Model_Memorandum_TransactionRow',['table_alias'=>'memo_str']);
			return $x->addCondition('memorandum_transaction_id',$q->getField('id'))
						->addCondition('amountDr','>',0)
						->_dsql()->del('fields')->field($q->expr('group_concat([0] SEPARATOR "<br/>")',[$x->getElement('account')]));
		})->allowHTML(true)->sortable(true);

		$model->addExpression('cr_account_no')->set(function($m,$q){
			$x = $m->add('Model_Memorandum_TransactionRow',['table_alias'=>'memo_str']);
			return $x->addCondition('memorandum_transaction_id',$q->getField('id'))
						->addCondition('amountCr','>',0)
						->_dsql()->del('fields')->field($q->expr('group_concat([0] SEPARATOR "<br/>")',[$x->getElement('account')]));
		})->allowHTML(true)->sortable(true);


		$model->getElement('created_at')->sortable(true);
		$crud = $this->add('CRUD');
		$crud->setModel($model);
		$crud->addRef('Memorandum_TransactionRow',['label'=>'Detail']);
		$crud->grid->addQuickSearch(['cr_account_no','dr_account_no','memorandum_type']);

		$crud->grid->addPaginator(50);
	}
}