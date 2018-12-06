<?php


class Model_Share extends Model_Table {
	public $table ='share';
	public $title_field = 'no';

	function init(){
		parent::init();

		$this->hasOne('Member','current_member_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->hasOne('ShareCertificate','share_certificate_id')->display(['form'=>'autocomplete/Basic'])->sortable(true);
		$this->addField('no')->type('number')->sortable(true);
		$this->addField('buyback_locking_months')->type('number')->defaultValue(BUYBACK_LOCKING_MONTHS);
		$this->addField('transfer_locking_months')->type('number')->defaultValue(TRANSFER_LOCKING_MONTHS);
		$this->addField('status')->enum(['Available','Reserved','Issued','AllowBuyBack','AllowTransfer'])->defaultValue('Available');

		$this->addExpression('recent_from_date')->set(function($m,$q){
			$his_m = $this->add('Model_ShareHistory',['rfd']);
			return $his_m->addCondition('share_id',$q->getField('id'))->setLimit(1)->setOrder('id','desc')->fieldQuery('from_date');
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

	function createNew($no_of_shares,$to_member_id=null,$start_no=null,$allot_share_certificate=true){

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

			$this->issueNewCertificates($to_member_id);

			$this->api->db->commit();
		}catch(Exception $e){
			$this->api->db->rollback();
			throw $e;
		}
	}

	function isTransferable($share_no){
		$m = $this->add('Model_Share')->loadBy('no',$share_no);
		// if set forced transfer or buyback as status
		$status_ok = (in_array($m['status'],['Issued','Available','AllowTransfer','AllowBuyBack']));
		// if not under locking period
		$buybacklocking_ok = strtotime($this->app->now) > strtotime($this->app->addDateDuration($m['buyback_locking_months'].' Months',$m['recent_from_date']));
		$transferlocking_ok = strtotime($this->app->now) > strtotime($this->app->addDateDuration($m['transfer_locking_months'].' Months',$m['recent_from_date']));
		
		// no liability
		$liability_ok = $this->add('Model_Active_Account_Loan')->addCondition('member_id',$m['current_member_id'])->count()->getOne() == 0;

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

	function transferOwnerShip($shares_array=[],$to_member_id){
		$shares_array = $this->convertShareArray($shares_array);
		try{
			$this->api->db->beginTransaction();
			foreach ($shares_array as $sno) {
				if(!$this->isTransferable($sno)) throw new \Exception("Share no ". $sno.' is non transferrable, Check Status, Lock Period or Loan Account for member', 1);
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
		$shares_array = $this->convertShareArray($shares_array);
		$model = $this->add('Model_Share')->addCondition('no',$shares_array);
		foreach ($model as $shares) {
			$shares['current_member_id'] = null;
			$shares['status']='Available';
			$shares['share_certificate_id']=null;
			$shares->save();
		}
	}

	function transfer($from_sm_account, $to_sm_account, $shares_array, $submitted_certificates){
		try{
			$this->api->db->beginTransaction();
			$shares_array = $this->convertShareArray($shares_array);
			$share_amount = count($shares_array) * RATE_PER_SHARE ;
		
			$narration = 'Share Transferred of Rs '.$share_amount.' from '. $from_sm_account['AccountNumber']. ' to '. $to_sm_account['AccountNumber']. ' ['.implode(",", $shares_array).']';
			$transaction = $this->add('Model_Transaction');
			// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
			$transaction->createNewTransaction(TRA_SHARE_TRANSFER,$from_sm_account->ref('branch_id'),$this->app->now,$narration);
			
			$transaction->addDebitAccount($from_sm_account,$share_amount);
			$transaction->addCreditAccount($to_sm_account,$share_amount);

			$transaction->execute();

			$this->transferOwnerShip($shares_array,$to_sm_account['member_id']);
			$this->submitCertificates($submitted_certificates);
			$this->issueNewCertificates($from_sm_account['member_id']);
			$this->issueNewCertificates($to_sm_account['member_id']);
			$this->api->db->commit();
		}catch(Exception $e){
			$this->api->db->rollback();
			throw $e;
		}
	}

	function buyBack($from_sm_account,$to_account,$shares_array, $submitted_certificates){
		try{
			$this->api->db->beginTransaction();
			$shares_array = $this->convertShareArray($shares_array);

			$share_amount = count($shares_array) * RATE_PER_SHARE ;
			
			$narration = 'Share Buy Backed of Rs '.$share_amount.' from '. $from_sm_account['AccountNumber']. ' to '. $to_account['AccountNumber']. ' ['.implode(",", $shares_array).']';
			$transaction = $this->add('Model_Transaction');
			// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
			$transaction->createNewTransaction(TRA_SHARE_BUYBACK,$from_sm_account->ref('branch_id'),$this->app->now,$narration);
			
			$transaction->addDebitAccount($from_sm_account,$share_amount);
			$transaction->addCreditAccount($to_account,$share_amount);

			$transaction->execute();

			$this->markAvailable($shares_array);
			$this->submitCertificates($submitted_certificates);
			$this->issueNewCertificates($from_sm_account['member_id']);
			$this->api->db->commit();
		}catch(Exception $e){
			$this->api->db->rollback();
			throw $e;
		}
	}

	function getCertificates($shares_array){
		if(!is_array($shares_array)) throw new \Exception("Shares Array must be an array", 1);
		
		$m=$this->add('Model_Share');
		$m->addCondition('no',$shares_array);		

		$certificates = array_column($m->getRows(), 'share_certificate');
		$certificates = array_unique($certificates);
		return $certificates;
	}

	function getOwnedShares($member_id){
		$m=$this->add('Model_Share');
		$m->addCondition('current_member_id',$member_id);
		$owned_share_nos = array_column($m->getRows(),'no');
		return $owned_share_nos;
	}

	function submitCertificates($submitted_certificates){
		$shares = $this->add('Model_Share')
			->addCondition('share_certificate',$submitted_certificates);

		foreach ($shares as $sh) {
			$sh['share_certificate_id'] = 0;
			$sh->saveAndUnload();
		}

		$this->add('Model_ShareCertificate')
			->addCondition('name',$submitted_certificates)
			->_dsql()
			->set('status','Submitted')
			->update();
	}

	// LOGIC: 
	// First we submit all certificates and marks share_certificates_id to null in all shares
	// Now we have empty values for these share_certificate_id scattered, so
	// just re issue A NE CERTIFICATE PER SHARES_LINE_IN_CERTIFICATE (4 in config now) shares
	// also mark this new certificate to current history of share to maintain history of certificate also

	function issueNewCertificates($member_id){
		// SHARES_LINE_IN_CERTIFICATE : constant
		$no_certificate_shares = $this->add('Model_Share');
		$no_certificate_shares->addCondition('current_member_id',$member_id);
		$no_certificate_shares->addCondition([['share_certificate_id',null],['share_certificate_id',0]]);
		$no_certificate_shares->setOrder('no asc');

		$shares_to_settle = $no_certificate_shares->count()->getOne();

		$i=0;
		$previous_no = null;
		$new_certificate = null;
		foreach ($no_certificate_shares as $sh) {
			if($previous_no==null || $new_certificate == null){
				$new_certificate = $this->add('Model_ShareCertificate')->createNew();
			}
			$sh['share_certificate_id'] = $new_certificate->id;
			$sh->currentHistoryEntry()->set('share_certificate_id',$new_certificate->id)->save();
			if($previous_no!==null && $sh['no'] != ($previous_no+1)) {
				// LOGIC: Certificate can print 4 lines only, one line is one share group (range or individual) 4-12,23,45-69,23
				// Then new certificate must be used for next 4 lines of share range.
				if($i % SHARES_LINE_IN_CERTIFICATE == 0){
					$new_certificate = null;
				}
				$i++;
			}
			$previous_no = $sh['no'];
			$sh->saveAndUnload();
		}

	}

	function hasOwnership($shares_array,$member_id){
		$shares_array = $this->convertShareArray($shares_array);
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
	function currentHistoryEntry(){
		return $this->ref('ShareHistory')
					->setLimit(1)
					->setOrder('from_date desc')
					->addCondition('final_to_date',null)
					->tryLoadAny()
					;
	}

	function convertShareArray($shares_array){
		$final_array=[];
		foreach ($shares_array as $sa) {
			if(strpos($sa, '-')){
				$range = array_map('trim', explode("-", $sa));
				if(count($range) !=2) throw new Exception("Range must be in format 'start-end'", 1);
				if($range[0] > $range[1]) throw new Exception("Range start must be lower than end", 1);
				for ($i=$range[0]; $i <= $range[1]; $i++) { 
					$final_array[] = $i;
				}
				
			}else{
				$final_array[] = (int)$sa;
			}
		}

		return array_unique($final_array);
	}

}