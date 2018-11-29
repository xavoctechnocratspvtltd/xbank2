<?php


class Model_Share extends Model_Table {
	public $table ='share';
	public $title_field = 'no';

	function init(){
		parent::init();

		$this->hasOne('Member','current_member_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->addField('no')->type('number')->sortable(true);
		$this->addField('buyback_locking_months')->type('number')->defaultValue(BUYBACK_LOCKING_MONTHS);
		$this->addField('transfer_locking_months')->type('number')->defaultValue(TRANSFER_LOCKING_MONTHS);
		$this->addField('status')->enum(['Available','Reserved','Issued','AllowBuyBack','AllowTransfer'])->defaultValue('Available');

		$this->addExpression('recent_from_date')->set(function($m,$q){
			return $m->refSQL('ShareHistory')->setLimit(1)->setOrder('id','desc')->fieldQuery('from_date');
		})->sortable(true);

		$this->addExpression('member_sm_account')->set(function($m,$q){
			return  $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])->addCondition('member_id',$q->getField('current_member_id'))->setLimit(1)->fieldQuery('AccountNumber');
		});

		$this->hasMany('ShareHistory','share_id');

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		// update share_member history if current_member_id is changed
		if($this->isDirty('current_member_id')){

			$current_member_id = $this['current_member_id'];

			if($this->loaded()){
				$old_share = $this->add('Model_Share')->load($this->id);
				// update _to_date of last mo assocition
				if($current_member_id != $old_share['current_member_id']){
					$asso = $this->add('Model_ShareHistory');
					$asso->addCondition('share_id',$this->id);
					$asso->addCondition('member_id',$old_share['current_member_id']);
					$asso->addCondition('from_date','<>',null);
					$asso->addCondition('final_to_date',null);
					$asso->tryLoadAny();
					if($asso->loaded()){
						$asso['final_to_date'] = $this->app->now;
						$asso->saveAndUnload();
					}
				}
			}

			// new share history entry
			if($current_member_id){
				$this->addHook('afterSave',function($m)use($current_member_id){
					$new_asso = $this->add('Model_ShareHistory');
					$new_asso['share_id'] = $m->id;
					$new_asso['member_id'] = $current_member_id;
					$new_asso['from_date'] = $m->app->now;
					$new_asso->save();
				});
			}
			
  		}
	}

	function beforeDelete(){
		if($this->ref('ShareHistory')->count()->getOne() > 0) {
			throw new Exception("Share contains Share History, Please remove ShareHistory First", 1);
			
		}
	}

	function createNew($no_of_shares,$to_member_id=null,$start_no=null){

		try{
		
			$this->api->db->beginTransaction();
			// use previously available shares first 

			$existing_shares = $this->add('Model_Share')->addCondition('status','Available')->setLimit($no_of_shares);
			foreach ($existing_shares as $exs) {
				$exs->issueTo($to_member_id);
				$no_of_shares--;
			}

			if(!$start_no) {
				$start_no = ($this->add('Model_Share')->setOrder('id','desc')->tryLoadAny()->get('no') + 1);
			}		

			$status='Available';
			if($to_member_id) $status='Issued';

			for ($i=0; $i < $no_of_shares; $i++) { 
				$new_m = $this->add('Model_Share');
				$new_m['no'] = $start_no;
				$new_m['status'] = $status;
				if($to_member_id) $new_m['current_member_id'] = $to_member_id;
				$new_m->save();

				$start_no++;
			}
			$this->api->db->commit();
		}catch(Exception $e){
			$this->api->db->rollback();
			throw $e;
		}
	}

	function isTransferable($share_no){
		$m = $this->add('Model_Share')->loadBy('no',$share_no);
		// if set forced transfer or buyback as status
		$status_ok = (in_array($m['status'],['Available','AllowTransfer','AllowBuyBack']));
		// if not under locking period
		$buybacklocking_ok = strtotime($this->app->now) > strtotime($this->app->addDateDuration($m['buyback_locking_months'].' Months',$m['recent_from_date']));
		$transferlocking_ok = strtotime($this->app->now) > strtotime($this->app->addDateDuration($m['transfer_locking_months'].' Months',$m['recent_from_date']));
		
		// no liability
		$liability_ok = true;

		$force_Allow = (in_array($m['status'], ['AllowTransfer','AllowBuyBack']));

		return $force_Allow or ($status_ok && $buybacklocking_ok && $transferlocking_ok && $liability_ok);
	}

	function issueTo($to_member_id){
		$m=$this->newInstance()->load($this->id);
		$m['current_member_id']=$to_member_id;
		$m['status']='Issued';
		$m->save();
		return $this;
	}

	function isAvalibale(){
		return $this['status'] =='Available';
	}

	function transferOwnerShip($share_nos=[],$to_member_id){
		try{
			$this->api->db->beginTransaction();
			foreach ($share_nos as $sno) {
				if(!$this->isTransferable($sno)) throw new \Exception("Share no ". $sno.' is non transferrable', 1);
				$m = $this->add('Model_Share')->loadBy('no',$sno);
				if($m->isAvalibale()) throw new Exception("Share is available, issue it, not transfer - ".$sno, 1);
				$m['current_member_id'] = $to_member_id;
				$m['status'] = 'Issued';
				$m->save();
			}
			$this->api->db->commit();
		}catch(Exception $e){
			$this->api->db->rollback();
			throw $e;
		}
	}

	function markAvailable($shares_array){
		$model = $this->add('Model_Share')->addCondition('no',$shares_array);
		foreach ($model as $shares) {
			$shares['current_member_id'] = null;
			$shares['status']='Available';
			$shares->save();
		}
	}

	function transfer($from_sm_account, $to_sm_account, $shares_array){
		$share_amount = count($shares_array) * RATE_PER_SHARE ;
		
	
		$narration = 'Share Transferred of Rs '.$share_amount.' from '. $from_sm_account['AccountNumber']. ' to '. $to_sm_account['AccountNumber']. ' ['.implode(",", $shares_array).']';
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_SHARE_TRANSFER,$from_sm_account->ref('branch_id'),$this->app->now,$narration);
		
		$transaction->addDebitAccount($from_sm_account,$share_amount);
		$transaction->addCreditAccount($to_sm_account,$share_amount);

		$transaction->execute();

		$this->transferOwnerShip($shares_array,$to_sm_account['member_id']);
	}

	function buyBack($from_sm_account,$to_account,$shares_array){
		$share_amount = count($shares_array) * RATE_PER_SHARE ;
		
		$narration = 'Share Buy Backed of Rs '.$share_amount.' from '. $from_sm_account['AccountNumber']. ' to '. $to_account['AccountNumber']. ' ['.implode(",", $shares_array).']';
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_SHARE_BUYBACK,$from_sm_account->ref('branch_id'),$this->app->now,$narration);
		
		$transaction->addDebitAccount($from_sm_account,$share_amount);
		$transaction->addCreditAccount($to_account,$share_amount);

		$transaction->execute();

		$this->markAvailable($shares_array);
	}



	function getOwnedShares($member_id){
		$m=$this->add('Model_Share');
		$m->addCondition('current_member_id',$member_id);
		$owned_share_nos = array_column($m->getRows(),'no');
		return $owned_share_nos;
	}

	function hasOwnership($shares_array,$member_id){
		if(!is_array($shares_array) OR count($shares_array)==0){
			throw new \Exception("Share array must be an array of atleast one element", 1);			
		}

		$owned_share_nos = $this->getOwnedShares($member_id);

		$not_owned= [];
		foreach ($shares_array as $s) {
			if(!in_array($s, $owned_share_nos)) $not_owned[]=$s;
		}

		if(count($not_owned)==0)
			return true;
		return $not_owned;
	}

}