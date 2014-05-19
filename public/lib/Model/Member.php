<?php

class Model_Member extends Model_Table {
	var $table= "members";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->defaultValue(@$this->api->current_branch->id);
		$this->addField('title')->enum(array('Mr.','Mrs.','Miss'));
		$this->addField('name')->mandatory(true);
		$this->addField('CurrentAddress');
		$this->addField('landmark');
		$this->addField('tehsil');
		$this->addField('district');
		$this->addField('city');
		$this->addField('pin_code');
		$this->addField('state');
		$this->addField('FatherName')->caption('Father / Husband Name');
		$this->addField('Cast');
		$this->addField('PermanentAddress')->type('text');
		$this->addField('Occupation')->enum(array('Business','Service','Self-Employed','Student','House Wife'));
		$this->addField('DOB')->type('date');
		// $this->addField('Age');
		$this->addField('Witness1Name');
		$this->addField('Witness1FatherName');
		$this->addField('Witness1Address');
		$this->addField('Witness2Name');
		$this->addField('Witness2FatherName');
		$this->addField('Witness2Address');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now)->group('system');
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now)->group('system');
		$this->addField('PhoneNos');
		$this->addField('IsMinor')->type('boolean');
		$this->addField('MinorDOB');
		$this->addField('ParentName');
		$this->addField('RelationWithParent');
		$this->addField('ParentAddress');
		$this->addField('FilledForm60')->caption('Filled Form 60/61')->type('boolean')->mandatory(true);
		$this->addField('PanNo');
		$this->addField('Nominee');
		$this->addField('RelationWithNominee');
		$this->addField('NomineeAge');

		// $this->addField('is_customer')->type('boolean')->mandatory(true);
		// $this->addField('is_member')->type('boolean')->mandatory(true)->defaultValue(true);
		$this->addField('is_agent')->type('boolean')->mandatory(true)->defaultValue(false)->group('system');

		$this->addExpression('age')->set(function($m,$q){
			return "25";
		});

		$this->hasMany('JointMember','member_id');
		$this->hasMany('Account','member_id');

		$this->addHook('beforeSave',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if(!$this['title'])
			throw $this->exception('Please Select Title', 'ValidityCheck')->setField('title');
		if(!$this['Occupation'])
			throw $this->exception('Please Select Occupation', 'ValidityCheck')->setField('Occupation');

	}

	function createNewMember($name, $admissionFee, $shareValue, $branch=null, $other_values=array(),$form=null,$on_date=null){
		if(!$on_date) $on_date = $this->api->now;
		if(!$branch) $branch = $this->api->current_branch;


		if($this->loaded()) throw $this->exception('Use Empty Model to create new Member');
		$this['name']=$name;
		$this['branch_id']=$branch->id;
		$this['created_at']=$on_date;
		foreach ($other_values as $field => $value) {
			$this[$field]=$value;
		}
		$this->save();

		if($admissionFee){
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction(TRA_NEW_MEMBER_REGISTRATIO_AMOUNT,$branch, $on_date, "Member Registration Fee ", null, array('reference_account_id'=>$this->id));
			
			$transaction->addDebitAccount($this->ref('branch_id')->get('Code').SP.CASH_ACCOUNT, $admissionFee);
			$transaction->addCreditAccount($this->ref('branch_id')->get('Code').SP.ADMISSION_FEE_ACCOUNT, $admissionFee);
			
			$transaction->execute();
		}

		if($shareValue){
			$sahre_capital_scheme = $this->add('Model_Scheme')->loadBy('name',CAPITAL_ACCOUNT_SCHEME);

			$new_sm_number = $sahre_capital_scheme->getNewSMAccountNumber();

			$share_account = $this->add('Model_Account');
			$share_account->createNewAccount($this->id, $sahre_capital_scheme->id ,$branch, $new_sm_number ,null,null,$on_date);
		}

	}

	function makeAgent($guarenters=array()){
		if(!$this->loaded()) throw $this->exception('Member must be loaded to make agent');

		// throw $this->exception(' Exception text', 'ValidityCheck')->setField('FieldName');
	}

	function removeAgent(){
		// throw $this->exception(' Exception text', 'ValidityCheck')->setField('FieldName');
	}

	function delete($forced=false){
		$stop_for_many=array('JointMember','Account');

		if(!$forced){
			if($this->ref('Account')->count()->getOne() > 0)
				throw $this->exception('Member Contains Accounts');
			if($this->ref('JointMember')->count()->getOne() > 0)
				throw $this->exception('Member is Joint with other accounts');
		}

		foreach ($a=$this->ref('Account') as $a_array) {
			$a->delete($forced);
		}

		foreach($jm=$this->ref('JointMember') as $jm_array){
			$jm->delete($forced);
		}

		parent::delete();
	}

}