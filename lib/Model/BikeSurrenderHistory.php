<?php



class Model_BikeSurrenderHistory extends Model_Table {
	public $table = 'bike_surrender_history';

	function init(){
		parent::init();


		$this->hasOne('Account','account_id');
		$this->addField('type')->enum(MANAGE_SURRENDER_HISTORY_FIELDS);
		// $this->addField('new_is_value')->type('boolean');
		$this->addField('new_date_value')->type('datetime');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}