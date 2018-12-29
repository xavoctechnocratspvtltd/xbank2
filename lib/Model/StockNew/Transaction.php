<?php

class Model_StockNew_Transaction extends Model_Table {
	public $table = 'stocknew_transactions';

	function init(){
		parent::init();

		$this->hasOne('StockNew_TransactionTemplate','transaction_template_type_id');
		$this->hasOne('Branch','from_branch_id');
		$this->hasOne('StockNew_Member','from_member_id');
		$this->hasOne('StockNew_Container','from_container_id');
		$this->hasOne('StockNew_ContainerRow','from_container_row_id');

		$this->hasOne('Branch','to_branch_id');
		$this->hasOne('StockNew_Member','to_member_id');
		$this->hasOne('StockNew_Container','to_container_id');
		$this->hasOne('StockNew_ContainerRow','to_container_row_id');

		$this->hasOne('StockNew_Item','item_id');
		$this->addField('qty')->defaultValue(0);
		$this->addField('rate')->type('money');
		$this->addField('narration')->type('text');

		$this->addField('created_at')->defaultValue($this->app->now);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}