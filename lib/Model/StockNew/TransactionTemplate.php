<?php


class Model_StockNew_TransactionTemplate extends Model_Table {
	public $table = 'stocknew_transactiontemplate';

	function init(){
		parent::init();

		$this->addField('name');

		$this->addField('from_branch')->type('boolean')->defaultValue(false);
		$this->addField('always_from_current_branch')->type('boolean')->defaultValue(false);
		$this->addField('from_supplier')->type('boolean')->defaultValue(false);
		$this->addField('from_staff')->type('boolean')->defaultValue(false);
		$this->addField('from_agent')->type('boolean')->defaultValue(false);
		$this->addField('from_dealer')->type('boolean')->defaultValue(false);
		$this->addField('from_container')->type('boolean')->defaultValue(false);
		$this->addField('from_container_types');
		$this->addField('from_containerrow')->type('boolean')->defaultValue(false);
		$this->addField('from_default_container_row')->type('boolean')->defaultValue(false);

		$this->addField('check_from_qty')->type('boolean')->defaultValue(true);

		$this->addField('to_branch')->type('boolean')->defaultValue(false);
		$this->addField('always_to_current_branch')->type('boolean')->defaultValue(false);
		$this->addField('to_supplier')->type('boolean')->defaultValue(false);
		$this->addField('to_staff')->type('boolean')->defaultValue(false);
		$this->addField('to_agent')->type('boolean')->defaultValue(false);
		$this->addField('to_dealer')->type('boolean')->defaultValue(false);
		$this->addField('to_container')->type('boolean')->defaultValue(false);
		$this->addField('to_container_types');
		$this->addField('to_containerrow')->type('boolean')->defaultValue(false);
		$this->addField('to_default_container_row')->type('boolean')->defaultValue(false);
		

		$this->addField('items')->type('boolean')->defaultValue(true);
		$this->addField('item_categories');
		
		$this->addField('qty')->type('boolean')->defaultValue(true);
		$this->addField('rate')->type('boolean')->defaultValue(false);;
		$this->addField('narration')->type('boolean')->defaultValue(true);

		$this->addField('subtract_qty_from')->type('boolean')->defaultValue(true);
		$this->addField('add_qty_to')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}