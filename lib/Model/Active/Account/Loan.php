<?php
class Model_Active_Account_Loan extends Model_Account_Loan{
	function init(){
		parent::init();
		$this->addCondition('ActiveStatus',true);
	}

	function getVehicalNo(){
		$doc = $this->add('Model_Document');
		$doc->addCondition('name','VEHICLE NO.');

		if(!$this->loaded() or !$doc->count()->getOne())
			return "Not Found";

		$doc->tryLoadAny();
		return $this->getDescription($doc->id);
	}

	function getChassisNo(){
		$doc = $this->add('Model_Document');
		$doc->addCondition('name','CHASSIS NO.');

		if(!$this->loaded() or !$doc->count()->getOne())
			return "Not Found";

		$doc->tryLoadAny();
		return $this->getDescription($doc->id);	
	}

	function getEngineNo(){
		$doc = $this->add('Model_Document');
		$doc->addCondition('name','ENGINE NO.');

		if(!$this->loaded() or !$doc->count()->getOne())
			return "No Found";

		$doc->tryLoadAny();
		return $this->getDescription($doc->id);
	}

	function getDescription($doc_id){
		$doc_sub_model = $this->add('Model_DocumentSubmitted');
		$doc_sub_model->addCondition('accounts_id',$this->id);
		$doc_sub_model->addCondition('documents_id',$doc_id);
		
		if($doc_sub_model->count()->getOne()){
			$doc_sub_model->tryLoadAny();
			return $doc_sub_model['Description'];
		}else
			return "Not Found";

	}

}