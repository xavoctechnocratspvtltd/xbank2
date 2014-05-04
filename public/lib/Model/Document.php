<?php
class Model_Document extends Model_Table {
	var $table= "documents";
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('SavingAccount')->type('boolean');
		$this->addField('FixedMISAccount')->type('boolean');
		$this->addField('LoanAccount')->type('boolean');
		$this->addField('RDandDDSAccount')->type('boolean');
		$this->addField('CCAccount')->type('boolean');
		$this->addField('OtherAccounts')->type('boolean');
		$this->hasMany('DocumentSubmitted','document_id');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}
}