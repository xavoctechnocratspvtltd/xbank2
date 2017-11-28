<?php
class Model_DocumentAll extends Model_Table {
	var $table= "documents";
	function init(){
		parent::init();

		$this->addField('name');

		$this->addField('MemberDocuments')->type('boolean');
		$this->addField('AgentDocuments')->type('boolean');
		$this->addField('DSADocuments')->type('boolean');
		
		$this->addField('AgentGuarantor')->type('boolean');
		$this->addField('DSAGuarantor')->type('boolean');

		$this->addField('SavingAccount')->type('boolean');
		$this->addField('FixedMISAccount')->type('boolean');
		$this->addField('LoanAccount')->type('boolean');
		$this->addField('RDandDDSAccount')->type('boolean');
		$this->addField('CCAccount')->type('boolean');
		$this->addField('OtherAccounts')->type('boolean');
		
		$this->addField('is_addable_by_staff')->type('boolean');
		$this->addField('is_editable_by_staff')->type('boolean');

		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->hasMany('DocumentSubmitted','documents_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function depositDocuments(){
		return $this->addCondition([['SavingAccount',true],['FixedMISAccount',true],['RDandDDSAccount',true]]);
	}

	function loanDocuments(){
		return $this->addCondition('LoanAccount',true);
	}

	function loadDocument($name){
		if(!$name) return false;
		$this->addCondition('name',$name);
		$this->tryLoadAny();
		return $this;
	}

}