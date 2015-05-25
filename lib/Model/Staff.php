<?php

class Model_Staff extends Model_Table {
	var $table= "staffs";

	function init(){
		parent::init();


		$this->hasOne('Branch','branch_id')->mandatory(true);
		$this->addField('username')->mandatory(true)->group('a~6~User Information');
		$this->addField('password')->display(array('grid'=>'password'))->group('a~6');
		$this->addField('name')->mandatory(true)->group('b~4~Basic Information');
		$this->addField('father_name')->mandatory(true)->group('b~4');
		$this->addField('mother_name')->group('b~4');
		$this->addField('is_active')->type('boolean')->defaultValue(true)->group('b~3');
		$this->addField('marriatal_status')->group('b~3');
		$this->addField('blood_group')->group('b~3');
		$this->addField('emp_code')->group('b~3');
		$this->addField('DOB')->group('b~3');
		$this->addField('mobile_no')->group('b~3');
		$this->addField('landline_no')->group('b~3');
		$this->addField('emergency_no')->group('b~3');
		$this->addField('pan_no')->group('b~3');
		$this->addField('role')->group('b~3');
		$this->addField('last_qualification')->group('b~4');
		$this->addField('designation')->group('b~4');
		$this->addField('present_address')->group('b~4')->type('text');
		$this->addField('parmanent_address')->group('b~4')->type('text');
		$this->addField('remark')->type('text')->group('b~4');

		$this->addField('pf_amount')->group('c~4~Account Information');
		$this->addField('basic_pay')->group('c~4');
		$this->addField('variable_pay')->group('c~4');
		$this->addField('created_at')->caption('Joining Date')->group('c~4');
		$this->addField('AccessLevel')->setValueList(array('100'=>'Super Admin','80'=>'CEO','60'=>'Branch Admin','40'=>'Power Staff', '20'=>'Staff','10'=>'Guest'))->mandatory(true)->DefaultValue(20)->group('c~4');
		$this->addField('amount_of_increment')->group('c~4');
		$this->addField('yearly_increment_amount')->group('c~4');
		$this->addField('salary')->group('c~4');
		$this->addField('relaving_date_if_not_active')->group('c~4');
		$this->addField('security_amount')->group('c~4');
		$this->addField('deposit_date')->type('date')->group('c~4');
		$this->addField('total_dep_amount')->group('c~4');
		$this->addField('posting_at')->group('c~4');
		$this->addField('pf_no')->group('c~4');
	
		$this->addField('bank_name')->group('d~5~Bank Information');
		$this->addField('ifsc_code')->group('d~3');
		$this->addField('account_no')->group('d~4');
		$this->addField('last_date_of_increment')->group('d~4');
		$this->addField('nominee_name')->group('e~4~Nominee Information');
		$this->addField('nominee_age')->group('e~4');
		$this->addField('relation_with_nominee')->group('e~4');

		$this->hasMany('Account','staff_id');
		$this->hasMany('Transaction','staff_id');
		$this->hasMany('Acl','staff_id');

		$this->add('Controller_Validator');
		$this->is(array(
							'username|to_trim|unique'
						)
				);


		$this->addHook('beforeDelete',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		
	}

	function isSuper(){
		return $this['AccessLevel']==100;
	}

	function isCEO(){
		return $this['AccessLevel'] == 80;
	}

	function acl(){
		return $this->ref('Acl');
	}

	function branch(){
		return $this->ref('branch_id');
	}

	function createNewStaff($name,$password,$AccessLevel,$branch_id=null){
		if(!$branch_id) $branch_id = $this->api->current_branch->id;
		if($this->loaded()) throw $this->exception('Use Empty Model to create new Staff');
		
		$this['username']= $name;
		$this['name'] = $name;
		$this['password'] = $password;
		$this['AccessLevel'] = $AccessLevel;
		$this['branch_id'] = $branch_id;
		$this->save();
	}

	function delete($forced=false){
		if(!$forced){
			if($this->ref('Transaction')->count()->getOne() > 0)
				throw $this->exception('Cannot delete Staff, contains transactions done by staff');
		}

		foreach($t=$this->ref('Transaction') as $t_array){
			$t->delete($forced,true);
		}
		parent::delete();
	}

	
}