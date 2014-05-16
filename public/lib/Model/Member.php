<?php

class Model_Member extends Model_Table {
	var $table= "members";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->defaultValue(@$this->api->current_branch->id);
		$this->addField('name')->mandatory(true);
		$this->addField('CurrentAddress');
		$this->addField('FatherName');
		$this->addField('Cast');
		$this->addField('PermanentAddress');
		$this->addField('Occupation');
		$this->addField('Age');
		$this->addField('Nominee');
		$this->addField('RelationWithNominee');
		$this->addField('NomineeAge');
		$this->addField('Witness1Name');
		$this->addField('Witness1FatherName');
		$this->addField('Witness1Address');
		$this->addField('Witness2Name');
		$this->addField('Witness2FatherName');
		$this->addField('Witness2Address');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('PhoneNos');
		$this->addField('PanNo');
		$this->addField('IsMinor');
		$this->addField('MinorDOB');
		$this->addField('ParentName');
		$this->addField('RelationWithParent');
		$this->addField('ParentAddress');
		$this->addField('DOB')->type('date');
		$this->addField('FilledForm60')->type('boolean')->mandatory(true);
		// $this->addField('is_customer')->type('boolean')->mandatory(true);
		// $this->addField('is_member')->type('boolean')->mandatory(true)->defaultValue(true);
		$this->addField('is_agent')->type('boolean')->mandatory(true)->defaultValue(false);

		$this->hasMany('Jointmember','member_id');
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNewMember($name,$createAdmissionFeeTransaction, $createSMAccount, $isAgent=null,$other_values=array(),$form=null,$on_date=null){
		if(!$on_date) $on_date = $this->api->now;

		if($this->loaded()) throw $this->exception('Use Empty Model to create new Member');
		$this['name']=$name;
		$this['created_at']=$on_date;
		foreach ($other_values as $field => $value) {
			$this[$field]=$value;
		}
		$this->save();

		if($createAdmissionFeeTransaction){
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_NEW_MEMBER_REGISTRATIO_AMOUNT,$this['branch_id'], $on_date, $Narration, $only_transaction, array('reference_account_id'=>$this->id));
			
			$transaction->addDebitAccount($account_model_or_number, $amount);
			$transaction->addCreditAccount($account_model_or_number, $amount);
			
			$transaction->execute();
		}

		if($createSMAccount){

		}

		if($isAgent){
			$this->makeAgent();
		}

		throw $this->exception('OPEN SM ACCOUNT');
		throw $this->exception('create agent');
	}

}