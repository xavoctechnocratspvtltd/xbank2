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
		$this->addField('allow_login')->type('boolean')->defaultValue(true)->display(array('grid'=>'grid/inline'));

		$this->hasMany('Staff','branch_id');
		$this->hasMany('Member','branch_id');
		$this->hasMany('Account','branch_id');
		$this->hasMany('Closing','branch_id');
		$this->hasMany('Mo','branch_id');
		$this->hasMany('Stock_Container','branch_id');

		$this->addHook('beforeSave',$this);
		$this->addHook('afterInsert',$this);
		$this->addHook('beforeDelete',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if(!isset($this->app->is_installing) && $this['Code']=='DFL'){
			throw $this->exception('Cannot Edit Default Branch');
		}
	}

	function beforeDelete(){
		if($this->ref('Staff')->count()->getOne() > 0)
				throw $this->exception('Branch Contains Staff, Cannot delete');

		if($this->ref('Member')->count()->getOne() > 0)
			throw $this->exception('Branch Contains Member, Cannot Delete');

		if($this->ref('Account')->count()->getOne() > 0)
			throw $this->exception('Branch Contains Accounts, cannot delete');
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

		$udr_branch_closing=$this->add('Model_Closing');
		$udr_branch_closing->tryLoadBy('branch_id',2);
		
		if($udr_branch_closing->loaded()){
			$closing_model=$this->add('Model_Closing');
			$closing_model['branch_id']=$id;
			$closing_model['daily']=$udr_branch_closing['weekly'];
			$closing_model['weekly']=$udr_branch_closing['weekly'];
			$closing_model['monthly']=$udr_branch_closing['monthly'];
			$closing_model['halfyearly']=$udr_branch_closing['halfyearly'];
			$closing_model['yearly']=$udr_branch_closing['yearly'];

			$closing_model->save();	
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
		$member->createNewMember($name=$this['Code']. SP . "Default", $admissionFee=false, $shareValue=false, $branch=$this, $other_values=array('Occupation'=>'Service'),$form=null,$on_date=null);
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

	function getCashAccount(){
		return $this->add('Model_Account')
			->addCondition('scheme_name',CASH_ACCOUNT_SCHEME)
			->addCondition('branch_id',$this->id)
			->loadAny();
	}

	function newVoucherNumber($branch=null, $transaction_date=null){
		$cross_check=false;

		if(!$branch) $branch = $this;

		$next_voucher_no = 'next_voucher_no_'. $branch->id;

		$f_year = $this->api->getFinancialYear($transaction_date);	
		$start_date = $f_year['start_date'];
		
		if(!$transaction_date){
			$end_date = $f_year['end_date'];
		}else{
			$end_date=$transaction_date;
			$cross_check=true;
		}

		if(isset($this->api->$next_voucher_no)){
			$fraction = explode(".",(string)$this->api->$next_voucher_no);
			if(count($fraction)==2){
				$fraction = $fraction[1];
				$fraction = str_replace("0.", "", $fraction);
				$fraction++;
				$this->api->$next_voucher_no = ((int) ($this->api->$next_voucher_no)) .'.'. $fraction;
				return $this->api->$next_voucher_no;
			}
			$this->api->$next_voucher_no = $this->api->$next_voucher_no + 1;
			return $this->api->$next_voucher_no;
		}


		$transaction_model = $this->add('Model_Transaction');
		$transaction_model->addCondition('branch_id',$branch->id);
		$transaction_model->addCondition('created_at','>=',$start_date);
		$transaction_model->addCondition('created_at','<',$this->api->nextDate($end_date)); // ! important next date

		$transaction_model->_dsql()->del('fields')->field('max(voucher_no)');

		$max_voucher = $transaction_model->_dsql()->getOne();
		
		
		if($cross_check){
			$max_voucher = (int) $max_voucher;
			$cross_check = $this->add('Model_Transaction');
			$cross_check->addCondition('branch_id',$branch->id);
			$cross_check->addCondition('voucher_no',$max_voucher+1);
			$cross_check->addCondition('created_at','>=',$start_date);
			$cross_check->addCondition('created_at','<',$this->api->nextDate($f_year['end_date'])); // ! important next date

			$cross_check->tryLoadAny();

			if($cross_check->loaded()){
				$cross_check_2 = $this->add('Model_Transaction');
				$cross_check_2->addCondition('branch_id',$branch->id);
				$cross_check_2->addCondition('voucher_no','like',round($max_voucher).'%');
				$cross_check_2->addCondition('created_at','>=',$start_date);
				$cross_check_2->addCondition('created_at','<',$this->api->nextDate($f_year['end_date'])); // ! important next date
				$max_voucher_check = $cross_check_2->count()->getOne();
				if($max_voucher_check > 0) {
					$this->api->$next_voucher_no = (string) ($max_voucher . ".". $max_voucher_check);
					return $this->api->$next_voucher_no;
				}
			}
		}

		$this->api->$next_voucher_no = $max_voucher + 1;
		return $max_voucher + 1 ;
	}

	function deleteForced(){
		foreach ($m=$this->ref('Member') as $m_array) {
			$m->deleteForced();
		}
		foreach ($s=$this->ref('Staff') as $s_array) {
			$s->deleteForced();
		}

		foreach ($a=$this->ref('Account') as $a_array) {
			$a->deleteForced();
		}
		parent::delete();
	}

	function performClosing($on_date=null, $test_scheme=null, $test_account = null){
		$this->api->markProgress('branch',0,$this['Code']);
		
		if(!$on_date) $on_date = $this->api->today;
		if(!$this->loaded()) throw $this->exception('Branch Must be loaded to perform closing');

		$on_date = date('Y-m-d',strtotime($on_date));
		$last_closing_date = $this->ref('Closing')->tryLoadAny()->get('daily');
		$last_half_yearly_closing_date = $this->ref('Closing')->tryLoadAny()->get('halfyearly');

		if(strtotime($on_date) <= strtotime($last_closing_date)){
			echo 'Daily Closing is already done before this date';
			return;
		}
		

		$diff=$this->api->my_date_diff($on_date,$last_closing_date);
		// echo $on_date. "<br>";
//		throw $this->exception($diff['days_total']);
		if($diff['days_total'] > 1)
			$this->performClosing(date('Y-m-d',strtotime($on_date.'-1 days')),$test_scheme, $test_account);
		
		$this->api->markProgress('daily',0,$on_date);
		$s=1;
		$schemeTypes = explode(',',ACCOUNT_TYPES);
		$this->api->markProgress('schemes',$s,'About to run schemes',count($schemeTypes));

		foreach ($schemeTypes as $st) {

			if($test_scheme and $test_scheme['SchemeType'] != $st) continue;

			$schemes = $this->add('Model_Scheme_'.$st)
				->setLimit(1); // We just need Model once to fire regarding functions, no need of traversing here

			foreach($schemes as $schm){
				$this->api->markProgress('schemes',$s++,$st,count($schemeTypes));
				// echo $schm['name'] ."<br/>";
				$schemes->daily($this, $on_date,$test_account);
				// echo "Daily <br/>";
				gc_collect_cycles();
				if($this->isQuarterEnd($on_date)){
					$this->api->markProgress('quarterly',0,$st);
					if(method_exists($schemes, 'quarterly'))
						$schemes->quarterly($this, $on_date, $test_account);
					$this->api->markProgress('quarterly',null);
				}

				gc_collect_cycles();

				if($this->is_MonthEndDate($on_date)){
					// echo "month end <br/>";
					$this->api->markProgress('monthly',0,$st);
					$schemes->monthly($this, $on_date,$test_account);
					$this->ref('Closing')->tryLoadAny()->set('monthly',$on_date)->save();
					$this->api->markProgress('monthly',null);
				}

				gc_collect_cycles();
				
				if($this->is_HalfYearEnding($on_date,$test_account)){
					// echo "hy end <br/>";
					$this->api->markProgress('halfyearly',0,$st);
					$schemes->halfYearly($this, $on_date,$test_account,$last_half_yearly_closing_date);
					$this->ref('Closing')->tryLoadAny()->set('halfyearly',$on_date)->save();
					$this->api->markProgress('halfyearly',null);
				}
				
				gc_collect_cycles();

				if($this->is_YearEnd($on_date)){
					// echo "YE end <br/>";
					$this->api->markProgress('yearly',0,$st);
					$schemes->yearly($this, $on_date,$test_account);
					$this->ref('Closing')->tryLoadAny()->set('yearly',$on_date)->save();
					$this->api->markProgress('yearly',null);
				}

				gc_collect_cycles();
			}
		}

		$this->ref('Closing')->tryLoadAny()->set('daily',$on_date)->save();
	}

	function is_MonthEndDate($on_date){
		if(strtotime($on_date) == strtotime(date("Y-m-t", strtotime($on_date))) )
			return true;
		else
			return false;
	}

	function isQuarterEnd($on_date){
		if(date('m',strtotime($on_date)) == 6 AND date('d',strtotime($on_date)) == 30 ) // June
			return true;
		if(date('m',strtotime($on_date)) == 9 AND date('d',strtotime($on_date)) == 30 ) //Sept
			return true;
		if(date('m',strtotime($on_date)) == 12 AND date('d',strtotime($on_date)) == 31 ) // Dec
			return true;
		if(date('m',strtotime($on_date)) == 3 AND date('d',strtotime($on_date)) == 31 ) // March
			return true;

		return false;
	}

	function is_HalfYearEnding($on_date){
		if(date('m',strtotime($on_date)) == 9 AND date('d',strtotime($on_date)) == 30 ) // Sept
			return true;
		if(date('m',strtotime($on_date)) == 3 AND date('d',strtotime($on_date)) == 31 )
			return true;

		return false;
	}

	function is_YearEnd($on_date){
		if(date('m',strtotime($on_date)) == 3 AND date('d',strtotime($on_date)) == 31 )
			return true;
		return false;		
	}

}