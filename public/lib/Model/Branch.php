<?php
class Model_Branch extends Model_Table {
	var $table= "branches";
	
	function init(){
		parent::init();

		$this->addField('name')->display(array('grid'=>'grid/inline'));
		$this->addField('Address');
		$this->addField('Code');
		$this->addField('PerformClosings')->type('boolean')->defaultValue(true)->display(array('grid'=>'grid/inline'));
		$this->addField('SendSMS')->type('boolean')->defaultValue(true);
		$this->addField('published')->type('boolean')->defaultValue(true);

		$this->hasMany('Staff','branch_id');
		$this->hasMany('Member','branch_id');
		$this->hasMany('Account','branch_id');

		$this->addHook('afterInsert',$this);

		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function afterInsert($branch,$id){
		$new_branch = $this->add('Model_Branch')->load($id);

		// Create Default Staff
		$new_branch->createDefaultStaff();
		
		// Create Default Member
		$new_branch->createDefaultMember();
		
		// Create All Existing Scheme's Default Accounts for this Branch
		foreach (explode(",",ACCOUNT_TYPES) as $schemeType) {
			$all_schemes_of_type = $this->add('Model_Scheme_'.$schemeType);
			foreach ($all_schemes_of_type as $scheme_array) {
				$all_schemes_of_type->createDefaultAccounts($new_branch);
			}
		}

		// Create Branch And Division Accounts for All Other Branches and 
		// its Branch And Division to all Other Branches 
		$other_branches=$this->add('Model_Branch');
		$other_branches->addCondition('id','<>',$new_branch->id);
		foreach ($other_branches as $other_branch_array) {
			$this->createBranchAndDivisionAccount($other_branches, $new_branch);
			$this->createBranchAndDivisionAccount($new_branch,$other_branches);
		}

	}

	function createBranchAndDivisionAccount($account_under_branch, $account_for_branch){
		if(!($account_under_branch instanceof Model_Branch) and !$account_under_branch->loaded()) throw $this->exception('Argument account_under_branch must be a loaded Branch Model');
		if(!($account_for_branch instanceof Model_Branch) and !$account_for_branch->loaded()) throw $this->exception('Argument account_for_branch must be a loaded Branch Model');
		
		$scheme=$this->add('Model_Scheme');
		$scheme->loadBy('name',BRANCH_AND_DIVISIONS);

		$account=$this->add('Model_Account');
		$account_number=$account_for_branch['Code'].SP.BRANCH_AND_DIVISIONS.SP.'for'.SP.$account_under_branch['Code'];
		$account->createNewAccount($account_under_branch->getDefaultMember()->get('id'),$scheme->id,$account_under_branch, $account_number,array('DefaultAC'=>1));

	}

	function createDefaultMember(){
		if(!$this->loaded()) throw $this->exception('Branch Must be loaded before creating default member');
		$member=$this->add('Model_Member');
		$member->createNewMember($name=$this['Code']. SP . "Default", $admissionFee=false, $shareValue=false, $branch=$this, $other_values=array(),$form=null,$on_date=null);
	}


	function createDefaultStaff(){
		if(!$this->loaded()) throw $this->exception('Branch Must be loaded before creating default staff');
		$defaultStaff = $this->add('Model_Staff');
		$defaultStaff->createNewStaff($this->api->normalizeName($this['name'].' admin'), 'admin',80,$this->id);


	}

	function getDefaultMember(){
		if(!$this->loaded()) throw $this->exception('Branch Must be loaded before getting default member');
		
		$member=$this->add('Model_Member');
		$member->loadBy('name',($this['Code']. SP . "Default"));
		return $member;
	}

	function getDefaultDealer(){
		
	}

	function newVoucherNumber($branch_id=null, $transaction_date=null){
		$cross_check=false;

		if(!$branch_id) $branch_id = $this->id;

		$f_year = $this->api->getFinancialYear($transaction_date);	
		$start_date = $f_year['start_date'];
		
		if(!$transaction_date){
			$end_date = $f_year['end_date'];
		}else{
			$end_date=$transaction_date;
			$cross_check=true;
		}


		$transaction_model = $this->add('Model_Transaction');
		$transaction_model->addCondition('branch_id',$branch_id);
		$transaction_model->addCondition('created_at','>=',$start_date);
		$transaction_model->addCondition('created_at','<',$this->api->nextDate($end_date)); // ! important next date

		$transaction_model->_dsql()->del('fields')->field('max(voucher_no)');

		$max_voucher = $transaction_model->_dsql()->getOne();
		
		if($cross_check){
			$cross_check = $this->add('Model_Transaction');
			$cross_check->addCondition('branch_id',$branch_id);
			$cross_check->addCondition('voucher_no',$max_voucher+1);
			$cross_check->addCondition('created_at','>=',$start_date);
			$cross_check->addCondition('created_at','<',$this->api->nextDate($f_year['end_date'])); // ! important next date

			$cross_check->tryLoadAny();

			if($cross_check->loaded()){
				$cross_check_2 = $this->add('Model_Transaction');
				$cross_check_2->addCondition('branch_id',$branch_id);
				$cross_check_2->addCondition('voucher_no','like',round($max_voucher).'%');
				$cross_check_2->addCondition('created_at','>=',$start_date);
				$cross_check_2->addCondition('created_at','<',$this->api->nextDate($f_year['end_date'])); // ! important next date				
				$max_voucher_check = $cross_check_2->count()->getOne();
				if($max_voucher_check > 0) 
					return $max_voucher + ( 0.1 * $max_voucher_check);
			}
			
		}

		return $max_voucher+1;
	}

	function delete($forced=false){
		if(!$forced){
			if($this->ref('Staff')->count()->getOne() > 0)
				throw $this->exception('Branch Contains Staff, Cannot delete');

			if($this->ref('Member')->count()->getOne() > 0)
				throw $this->exception('Branch Contains Member, Cannot Delete');

			if($this->ref('Account')->count()->getOne() > 0)
				throw $this->exception('Branch Contains Accounts, cannot delete');
		}

		foreach ($m=$this->ref('Member') as $m_array) {
			$m->delete($forced);
		}
		foreach ($s=$this->ref('Staff') as $s_array) {
			$s->delete($forced);
		}

		foreach ($a=$this->ref('Account') as $a_array) {
			$a->delete($forced);
		}
		parent::delete();
	}
}