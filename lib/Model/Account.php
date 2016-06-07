<?php

class Model_Account extends Model_Table {
	var $table= "accounts";
	public $scheme_join=null;
	public $allow_any_name = false;
	
	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->mandatory(true)->display(array('form'=>'Member'));
		$this->hasOne('Scheme','scheme_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','loan_from_account_id')->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','account_to_debit_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','related_account_id')->system(true);
		$this->hasOne('Account','intrest_to_account_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','MaturityToAccount_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','agent_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','collector_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','LoanAgainstAccount_id')->display(array('form'=>'autocomplete/Basic'))->defaultValue('0')->sortable(true);
		$this->hasOne('Dealer','dealer_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));

		$this->hasOne('Branch','branch_id')->mandatory(true)->defaultValue(@$this->api->current_branch->id)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Staff','staff_id')->mandatory(true)->defaultValue(@$this->api->auth->model->id)->display(array('form'=>'autocomplete/Basic'));
		
		$this->hasOne('Mo','mo_id')->display(array('form'=>'autocomplete/Basic'))->defaultValue(0);
		$this->hasOne('Team','team_id')->display(array('form'=>'autocomplete/Basic'))->defaultValue(0);
		// $this->hasOne('Member','collector_id')->display(array('form'=>'autocomplete/Basic'));		
		
		//New Fields added//
		$this->addField('account_type');
		$this->addField('AccountNumber')->sortable(true);//->display(array('form'=>'Readonly'));//->mandatory(true);
		$this->addField('AccountDisplayName')->caption('Account Displ. Name');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true)->sortable(true);

		$this->addField('ModeOfOperation')->setValueList(array('Self'=>'Self','Joint'=>'Joint'))->defaultValue('Self')->caption('Operation Mode');
		
		//New Fields added//
		$this->addField('LoanInsurranceDate')->type('datetime');
		
		$this->addField('OpeningBalanceDr')->type('money')->defaultValue(0);
		$this->addField('OpeningBalanceCr')->type('money')->defaultValue(0);
		$this->addField('ClosingBalance')->type('money')->defaultValue(0);
		$this->addField('CurrentBalanceDr')->type('money')->defaultValue(0);
		$this->addField('CurrentInterest')->type('money')->defaultValue(0);
		$this->addField('Nominee');
		$this->addField('NomineeAge');
		$this->addField('RelationWithNominee')->enum(array('Father','Mother','Husband','Wife','Brother','Sister','Son','Daughter','Other'));
		$this->addField('MinorNomineeDOB');
		$this->addField('MinorNomineeParentName')->type('text')->caption('Minor Nominee Parents Details');
		$this->addField('DefaultAC')->type('boolean')->defaultValue(false);
		$this->addField('created_at')->type('datetime')->sortable(true);//->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime');//->defaultValue($this->api->now);
		$this->addField('CurrentBalanceCr')->type('money');
		$this->addField('LastCurrentInterestUpdatedAt')->type('datetime');//->defaultValue($this->api->now);
		// $this->addField('InterestToAccount')->type('int'); now converted to hasOne Account
		$this->addField('Amount')->type('money')->defaultValue(0);
		$this->addField('LockingStatus')->type('boolean')->defaultValue(false);
		$this->addField('affectsBalanceSheet')->type('boolean')->defaultValue(true);
		$this->addField('MaturedStatus')->type('boolean')->defaultValue(false);
		$this->addField('Group');
		$this->addField('PAndLGroup')->system(true);
		
		$this->addField('is_dirty')->type('boolean')->system(true)->defaultValue(false);
		
		$this->addField('extra_info')->type('text')->system(true); // Put json style extra info in this field

		$this->scheme_join = $this->leftJoin('schemes','scheme_id');
		$this->scheme_join->addField('SchemeType');
		$this->scheme_join->addField('scheme_name','name');

		$this->add('filestore/Field_Image','doc_image_id')->type('image')->mandatory(true);
		$this->add('filestore/Field_Image','sig_image_id')->type('image')->mandatory(true);

		$this->addExpression('branch_code')->set(function($m,$q){
			return $m->refSQL('branch_id')->fieldQuery('Code');
		});
		$this->addExpression('member_name_only')->set($this->refSQL('member_id')->fieldQuery('member_name_only'))->caption('Member Name');
		$this->addExpression('name')->set(function($m,$q){
			
			$member = $m->add('Model_Member',array('table_alias'=>'account_holder'));
			$member->addCondition('id',$q->getField('member_id'));
			$member->_dsql()->del('fields')->field('name');

			$member_father = $m->add('Model_Member',array('table_alias'=>'account_holder_father'));
			$member_father->addCondition('id',$q->getField('member_id'));
			$member_father->_dsql()->del('fields')->field('FatherName');

			// return 'AccountNumber';

			return '(CONCAT(
								'. $q->getField('AccountNumber') .',
								" : ",
								('.$member->_dsql()->render().'),
								" [ ",
								IFNULL(('.$member_father->_dsql()->render().'),"NA"),
								" ] ",
								" - ",
								IFNULL('.$q->getField('AccountDisplayName').',"")
							)
					)';
		});

		$this->addExpression('crpb')->set(function($m,$q){
			return $q->expr('[0]*[1] / 100.00',array($m->getElement('Amount'),$m->refSQL('scheme_id')->fieldQuery('CRPB')));
		});

		// $member_join = $this->leftJoin('members','member_id');
		// $member_join->addField('member_name','name');
		// $member_join->addField('FatherName');

		// $this->debug();

		$this->hasMany('JointMember','account_id');
		$this->hasMany('Premium','account_id');
		$this->hasMany('DocumentSubmitted','accounts_id');
		$this->hasMany('AccountGuarantor','account_id');
		$this->hasMany('TransactionRow','account_id');
		$this->hasMany('Account','related_account_id',null,'RelatedAccounts');
		$this->hasMany('Transaction','reference_id',null,'RelatedTransactions');
		$this->hasMany('Comment','account_id');

		$this->addHook('beforeSave',array($this,'defaultBeforeSave'));
		$this->addHook('beforeDelete',array($this,'defaultBeforeDelete'));
		$this->addHook('editing',array($this,'editing_default'));


		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function editing_default(){
		$this->getElement('scheme_id')->system(true);
		if( !$this->api->currentStaff->isSuper()){
			$this->getElement('AccountNumber')->system(true);
		}
		$this->getElement('account_type')->system(true);
		$this->getElement('Amount')->system(true);
		$this->getElement('ModeOfOperation')->system(true);
	}

	function defaultBeforeSave(){

		// throw new \Exception($this['account_type'], 1);
		if(!$this['DefaultAC'] AND strpos($this['AccountNumber'], 'SM') !==0 AND !$this->allow_any_name AND !$this->loaded() ){
			$start_code = ($this->ref('branch_id')->get('Code').$this->api->getConfig('account_code/'.$this['account_type']));
			if(strpos($this['AccountNumber'], $start_code) !==0)
				throw $this->exception('AccountNumber Format not accpeted, Must start with '. $start_code.' or SM whie it is '. $this['AccountNumber'],'ValidityCheck')->setField('AccountNumber')->addMoreInfo('acc',$this['AccountNumber']);//->setField('AccountNumber');
		}

		// if(!$this['DefaultAC'] AND strpos($this['AccountNumber'], 'SM') !==0 and substr($this['AccountNumber'],0,3) !== $this->api->current_branch['Code'])
		// 	throw $this->exception('AccountNumber Format not accpeted, Must Have This account Code in Start','ValidityCheck')->addMoreInfo('acc',$this['AccountNumber'])->setField('AccountNumber');

		// PandLGroup set default
		if(!$this['Group'])
			$this['Group'] = $this->add('Model_Scheme')->load($this['scheme_id'])->get('SchemeGroup');
		if(!$this['PAndLGroup'])
			$this['PAndLGroup'] = $this['Group'];

		if($this->loaded() and $this->dirty['AccountNumber']){
			$old_acc = $this->newInstance()->load($this->id);
			$transactions = $this->add('Model_Transaction');
			$transactions->dsql()->expr('UPDATE transactions SET Narration=REPLACE(Narration,"'.$old_acc['AccountNumber'].'","'.$this['AccountNumber'].'") WHERE Narration like "%'.$old_acc['AccountNumber'].'%"')->execute();
		}

	}

	function defaultBeforeDelete(){
		if($this->table =='accounts_pending') return;

		if($this->ref('TransactionRow')->count()->getOne() > 0)
			throw $this->exception('Account Contains Transactions, Cannot Delete');

		if($related_tran = $this->ref('RelatedTransactions')->addCondition('cr_sum','<>',0)->count()->getOne()){
			$related_tran = $this->ref('RelatedTransactions')->addCondition('cr_sum','<>',0)->tryLoadAny();
			throw $this->exception('Related Transaction found')
						->addMoreInfo('Narartion',$related_tran['Narration'])
						->addMoreInfo('created_at',$related_tran['created_at'])
						->addMoreInfo('In Branch',$related_tran['branch'])
						->addMoreInfo('Transaction Type',$related_tran['transaction_type']);
		}


		$this->ref('Premium')->deleteAll();
		$this->ref('JointMember')->deleteAll();
		// $this->ref('Premium')->deleteAll();
		$this->ref('DocumentSubmitted')->deleteAll();
		$this->ref('AccountGuarantor')->deleteAll();
		$this->ref('Comment')->deleteAll();
		$this->ref('RelatedTransactions')->deleteAll();

	}

	function debitWithTransaction($amount,$transaction_id,$only_transaction=null,$no_of_accounts_in_side=null){

		$transaction_row=$this->add('Model_TransactionRow');
		$transaction_row['amountDr']=$amount;
		$transaction_row['side']='DR';
		$transaction_row['transaction_id']=$transaction_id;
		$transaction_row['account_id']=$this->id;
		// $transaction_row['accounts_in_side']=$no_of_accounts_in_side;
		$transaction_row->save();

		if($only_transaction) return;
		
		$this->debitOnly($amount);
	}

	function creditWithTransaction($amount,$transaction_id,$only_transaction=null,$no_of_accounts_in_side=null){

		$transaction_row=$this->add('Model_TransactionRow');
		$transaction_row['amountCr']=$amount;
		$transaction_row['side']='CR';
		$transaction_row['transaction_id']=$transaction_id;
		$transaction_row['account_id']=$this->id;
		// $transaction_row['accounts_in_side']=$no_of_accounts_in_side;
		$transaction_row->save();

		if($only_transaction) return;
		
		$this->creditOnly($amount);
	}

	function debitOnly($amount){ 
		$this->hook('beforeAccountDebited',array($amount));
		$this['CurrentBalanceDr']=$this['CurrentBalanceDr']+$amount;
		$this->save();
		$this->hook('afterAccountDebited',array($amount));
		$this->closeIfPaidCompletely();		
	}

	function creditOnly($amount){
		$this->hook('beforeAccountCredited',array($amount));
		$this['CurrentBalanceCr']=$this['CurrentBalanceCr']+$amount;
		$this->save();
		$this->hook('afterAccountCredited',array($this,$amount));
		$this->closeIfPaidCompletely();		
	}

	function closeIfPaidCompletely(){

		if($this->isFD() || $this->isMIS() || $this->isDDS() || $this->isLoan() || $this->isRecurring()){
			
			if (($this['CurrentBalanceDr'] - $this['CurrentBalanceCr']) == 0) {
			    $this['ActiveStatus'] = false;
			    $this['affectsBalanceSheet'] = true;
			    $this['MaturedStatus'] = true;
			    $this->save();
			}
		}
	}

	function createNewPendingAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=null,$form=null,$created_at=null){
		if(!($branch instanceof Model_Branch) or !$branch->loaded()) throw $this->exception('Branch Must be Loaded Object of Model_Branch');
		if(!$created_at) $created_at = $this->api->now;
		if(!$otherValues) $otherValues=array();

		if($otherValues['account_type']==LOAN_AGAINST_DEPOSIT){
			if(!$otherValues['LoanAgainstAccount_id'])
				throw $this->exception('Please Specify Loan Against Account Number', 'ValidityCheck')->setField('LoanAgainstAccount');

		}else{
			if(!$otherValues['dealer_id'])
				throw $this->exception('Dealer is Must', 'ValidityCheck')->setField('dealer');
		}

		$pending_account = $this->add('Model_PendingAccount');
		$pending_account->allow_any_name = true;

		$pending_account['member_id'] = $member_id;
		$pending_account['scheme_id'] = $scheme_id;
		$pending_account['AccountNumber'] = 'new_account '.$this->api->currentBranch->id.date('YmdHis').rand(1000,9999);
		$pending_account['branch_id'] = $branch->id;
		$pending_account['created_at'] = $created_at;
		$pending_account['LastCurrentInterestUpdatedAt']=isset($otherValues['LastCurrentInterestUpdatedAt'])? :$created_at;

		unset($otherValues['member_id']);
		unset($otherValues['scheme_id']);
		unset($otherValues['AccountNumber']);
		unset($otherValues['branch_id']);
		unset($otherValues['created_at']);
		unset($otherValues['LastCurrentInterestUpdatedAt']);

		foreach ($otherValues as $field => $value) {
			$pending_account[$field] = $value;
		}

		$extra_info=array();
		
		$joint_members=array();
		for($k=2;$k<=4;$k++) {
		    if($j_m_id=$otherValues['member_ID'.$k])
		    	$joint_members[] = $j_m_id;
		}

		$documents=$this->add('Model_Document');
		$documents_feeded = array();
		foreach ($documents as $d) {
		 	if($form[$this->api->normalizeName($documents['name'])]){
				$documents_feeded[$documents['name']]=$form[$this->api->normalizeName($documents['name'].' value')];
		 	}
		}

		$extra_info['joint_members'] = $joint_members;
		$extra_info['documents_feeded'] = $documents_feeded;
		$extra_info['loan_from_account'] = $otherValues['loan_from_account'];
		
 		$pending_account['extra_info'] = json_encode($extra_info);
		$pending_account->save();

		return $pending_account;
	}

	function getNewAccountNumber($account_type=null,$branch=null){
		
		if(!$account_type) $account_type = $this['account_type'];
		if(!$account_type) throw $this->exception('Could not Identify Account Type to generate Account Number', 'ValidityCheck')->setField('AccountNumber');
        if(!$branch) $branch= $this->api->currentBranch;

		$ac_code = $this->api->getConfig('account_code/'.$account_type,false);
		if(!$ac_code) throw $this->exception('Account type Code is not proper ')->addMoreInfo('Account account_type',$this['account_type']);

		$prefix_length = 3+strlen($ac_code); // BRANCH CODE + SB/DDS/MIS ...

		$max_account_number = $this->add('Model_Account');
		$new_number = $max_account_number->_dsql()->del('fields')
			->field($this->dsql()->expr('	MAX(
                                                                CAST(
                                                                        SUBSTRING(
                                                                                AccountNumber,
                                                                                '.($prefix_length+1).',
                                                                                LENGTH(AccountNumber) - '.($prefix_length-1).'
                                                                        ) AS UNSIGNED
                                                                )
                                                        )'))
			->where('LEFT(AccountNumber,3) = "'.$branch['Code'].'"')
                        ->where('account_type',$account_type)
			->getOne();
               
        // throw new Exception($new_number, 1);
        
		return $branch['Code'].$ac_code.($new_number+1);
	}

	/**
	 * Create new account on an empty AccountModel, overrided in child classes but required to call this parent::function
	 * @param  id $member_id     
	 * @param  id $scheme_id     
	 * @param  Model_Branch $branch
	 * @param  String $AccountNumber 
	 * @param  array  [$otherValues]   
	 * @param  Form_As_Array [$form]   
	 * @param  String [$created_at]
	 * @return id New account model id
	 */
	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=null,$form=null,$created_at=null){
		// if($this['account_type']!=createNewAccount)
		if(!($branch instanceof Model_Branch) or !$branch->loaded()) throw $this->exception('Branch Muct be Loaded Object of Model_Branch');
		if(!$created_at) $created_at = $this->api->now;
		if(!$otherValues) $otherValues=array();

		$this['member_id'] = $member_id;
		$this['scheme_id'] = $scheme_id;
		$this['AccountNumber'] = $AccountNumber;
		$this['branch_id'] = $branch->id;
		$this['created_at'] = $created_at;
		$this['LastCurrentInterestUpdatedAt']=isset($otherValues['LastCurrentInterestUpdatedAt'])? :$created_at;

		unset($otherValues['member_id']);
		unset($otherValues['scheme_id']);
		unset($otherValues['AccountNumber']);
		unset($otherValues['branch_id']);
		unset($otherValues['created_at']);
		unset($otherValues['LastCurrentInterestUpdatedAt']);

		foreach ($otherValues as $field => $value) {
			if(!is_array($value))
				$this[$field] = $value;
		}

		$this->save();
		for($k=2;$k<=4;$k++) {
		    if($j_m_id=$otherValues['member_ID'.$k])
		    	$this->jointAccountMember($j_m_id);
		}
		return $this->id;
	}

	function jointAccountMember($j_m_id){
		if(!$this->loaded()) throw $this->exception('Account Must Be loaded to add Joint Member');
		$member = $this->add('Model_Member')->load($j_m_id);
		$joint_member = $this->ref('JointMember')->addCondition('member_id',$j_m_id);
		$joint_member->tryLoadAny();
		if($joint_member->loaded())
			throw $this->exception($member['name'].' Already Joint with account '. $this['AccountNumber']);
		else
			$joint_member->save();
	}

	function updateDocument($document,$value){
		$document_submitted = $this->add('Model_DocumentSubmitted');
		$document_submitted->addCondition('documents_id',$document->id);
		$document_submitted->addCondition('accounts_id',$this->id);
		$document_submitted->tryLoadAny();
		
		if($value=='') throw $this->exception('Value Must Be Filled for '. $document['name'],'ValidityCheck')->setField($this->api->normalizeName($document['name'].' value'));

		$document_submitted['Description'] = $value;
		$document_submitted->save();
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');
		if(!isset($this->transaction_deposit_type)) throw $this->exception('transaction_deposit_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		if(!isset($this->default_transaction_deposit_narration)) throw $this->exception('default_transaction_deposit_narration must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);

		if(!trim($narration)) $narration = str_replace("{{AccountNumber}}", $this['AccountNumber'],str_replace('{{SchemeType}}', $this['SchemeType'], $this->default_transaction_deposit_narration));
		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$accounts_to_debit) $accounts_to_debit = array();
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction($this->transaction_deposit_type,$in_branch,$transaction_date,$narration,null,array('reference_id'=>$this->id));
		
		$transaction->addCreditAccount($this,$amount);			

		if(count($accounts_to_debit)){
			foreach ($accounts_to_debit as $debit_info) {
				if(!is_array($debit_info)) throw $this->exception('Provided information must be array');
				foreach ($debit_info as $account => $amount) {
					$transaction->addDebitAccount($account,$amount);
				}
			}
		}else{				
			$transaction->addDebitAccount($in_branch['Code'].SP.CASH_ACCOUNT,$amount);
		}
		$transaction->execute();

		return $transaction->id;

	}

	function creditedAmount($from_date=null,$to_date=null){
		$sum = $this->ref('TransactionRow');
		if($from_date)
			$sum->addCondition('created_at','>=', $from_date);
		if($to_date)
			$sum->addCondition('created_at','<', $this->api->nextDate($to_date));

		$sum = $sum->sum('amountCr')->getOne();
		return $sum;
	}

	function debitedAmount($from_date,$to_date){
		$sum = $this->ref('TransactionRow')
				->addCondition('created_at','>=', $from_date)
				->addCondition('created_at','<', $this->api->nextDate($to_date))
				->sum('amountDr')->getOne();
		return $sum;
	}

	function agent(){
		$agent = $this->ref('agent_id');
		if($agent->loaded()) return $agent;
		return false;
	}

	function collectionAgent(){
		if(!$this['collector_id'])
			return $this->agent();

		$agent = $this->ref('collector_id');
		if($agent->loaded()) return $agent;
		
		return false;
	}

	function scheme(){
		$scheme = $this->ref('scheme_id');
		if($scheme->loaded()) return $scheme;
		return false;
	}

	function clean(){
		$this['is_dirty']=false;
		$this->save();
	}

	function markDirty(){
		$this['is_dirty']=true;
		$this->save();	
	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null,$in_branch=null,$reference_id=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Withdrawing amount');
		if(!isset($this->transaction_withdraw_type)) throw $this->exception('transaction_withdraw_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		if(!isset($this->default_transaction_withdraw_narration)) throw $this->exception('default_transaction_withdraw_narration must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);

		if(!$this['sig_image_id']) throw $this->exception('No Signature Found, Cannot Withdraw','ValidityCheck')->setField('account');

		if(!trim($narration)) $narration = str_replace("{{AccountNumber}}", $this['AccountNumber'],str_replace("{{SchemeType}}", $this['SchemeType'], $this->default_transaction_withdraw_narration));
		if(!$on_date) $on_date = $this->api->now;
		if(!$accounts_to_credit OR !is_array($accounts_to_credit)) $accounts_to_credit = array();
		if(!$in_branch) $in_branch = $this->api->current_branch;

		if(!$amount) throw $this->exception('Amount not accepted','ValidityCheck')->setField('amount');

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction($this->transaction_withdraw_type,$in_branch,$on_date,$narration,null,array('reference_id'=>$this->id));
		
		$transaction->addDebitAccount($this,$amount);			

		if(count($accounts_to_credit)){
			foreach ($accounts_to_credit as $credit_info) {
				if(!is_array($credit_info)) throw $this->exception('Provided information must be array');
				foreach ($credit_info as $account => $amount) {
					$transaction->addCreditAccount($account,$amount);
				}
			}
		}else{				
			$transaction->addCreditAccount($in_branch['Code'].SP.CASH_ACCOUNT,$amount);
		}
		$transaction->execute();

		return $transaction->id;

	}

	function propogateAgentCommission($debit_account, $total_commission_amount, $on_date=null){
		
		if(!$on_date) $on_date= $this->api->today;
		
		$agent = $this->agent();
		while($sponsor = $agent->sponsor()){
			
			$percentage = $sponsor->cadre()->cumulativePercantage($agent->cadre());
			$commissionForThisAgent = $total_commission_amount * $percentage / 100;

			$transaction = $this->add('Model_Transaction');
	        $transaction->createNewTransaction(TRA_ACCOUNT_OPEN_AGENT_COMMISSION, $this->ref('branch_id'), $on_date, "Agent Account openning commision for ".$this['AccountNumber'], $only_transaction=null, array('reference_id'=>$this->id));
	        
	        $transaction->addDebitAccount($debit_account, $commissionForThisAgent);

	        $agent_saving_account = $sponsor->ref('account_id');
	        $tds_account = $this->add('Model_Account')->loadBy('AccountNumber',$this['branch_code'].SP.BRANCH_TDS_ACCOUNT);

	        $tds_amount = (strlen($agent_saving_account->ref('member_id')->get('PanNo'))==10)? $commissionForThisAgent * 10 /100 : $commissionForThisAgent * 20 /100;
			
			$saving_amount = $commissionForThisAgent - $tds_amount;

	        $transaction->addCreditAccount($agent_saving_account, $saving_amount);
	        $transaction->addCreditAccount($tds_account, $tds_amount);
	        
	        $transaction->execute();

	        $agent = $sponsor;

		}		

	}



	function conveyance($staff,$amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$account_cr = $this->add('Model_Account')
										->load($amount_from_account);
		$account_dr = $this->add('Model_Account')
										->tryLoadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'Conveyance Expenses');

		if(!$account_dr->loaded()){
			$scheme = $this->add('Model_Scheme');
			$scheme->loadBy('name','indirect expenses');
			$account_dr->createNewAccount($in_branch->getDefaultMember()->get('id'),$scheme->id,$in_branch,$in_branch['Code'].SP.'Conveyance Expenses',array('DefaultAC'=>true,'Group'=>'Conveyance Expenses','PAndLGroup'=>'Conveyance Expenses'));
		}

		$staff_model=$this->add('Model_Employee')->load($staff);
		
		if(!$narration)
			$narration = "Conveyance Amount paid to ";

		$narration.= " - ".$staff_model['name'];
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_CONVEYANCE_CAHRGES,$in_branch,$transaction_date,$narration,null,array('reference_id'=>$staff));
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);			

		$transaction->execute();

		return $transaction->id;

	}


	function fuel($staff, $amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$staff_model=$this->add('Model_Employee')->load($staff);

		if(!$narration)
			$narration = "Fuel Amount paid to ";

		$narration .= " - ".$staff_model['name'];

		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'FUEL EXPENSES');
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_FUEL_CAHRGES,$in_branch,$transaction_date,$narration,null,array('reference_id'=>$staff));
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);			

		$transaction->execute();

		return $transaction->id;

	}

	function legalChargePaid($amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;


		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'LEGAL EXPENSES PAID');
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		if(!$narration) $narration = 'Legal Charges Paid in '. $account_cr['AccountNumber'];

		$transaction->createNewTransaction(TRA_LEGAL_CHARGE_PAID,$in_branch,$transaction_date,$narration);
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);			

		$transaction->execute();

		return $transaction->id;

	}



	function legalChargeReceived($amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'LEGAL EXPENSES RECEIVED');
		$transaction = $this->add('Model_Transaction');
		if(!$narration) $narration = 'Legal Charges Debited in '. $account_dr['AccountNumber'];
		
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_LEGAL_CHARGE_RECEIVED,$in_branch,$transaction_date,$narration);
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);			

		$transaction->execute();

		return $transaction->id;

	}

	function visitCharge($amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'Visit Charge');
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		$transaction->createNewTransaction(TRA_VISIT_CHARGE,$in_branch,$transaction_date,$narration);
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);			

		$transaction->execute();

		return $transaction->id;

	}

	function forcloseTransaction($amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;

		
		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'For Closed');
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_id'=>$this->id));
		if(!$narration) $narration = 'For Close Charges Debited in '. $account_cr['AccountNumber'];

		$transaction->createNewTransaction(TRA_FORCLOSE_CHARGE,$in_branch,$transaction_date,$narration);
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);			

		$transaction->execute();

		return $transaction->id;

	}

	function postInterestEntry(){

	}

	function postAgentCommission(){
		throw $this->exception('Interest Receive must be redefined in account if required', 'ValidityCheck')->setField('FieldName');
	}

	function postPanelty(){
		throw $this->exception('Post Panelty must be redefined in account if required', 'ValidityCheck')->setField('FieldName');
	}

	function forClose(){
		throw $this->exception('For Close must be redefined in account if required', 'ValidityCheck')->setField('FieldName');
	}

	function markMature(){
		throw $this->exception('Mark Mature must be redefined in account if required', 'ValidityCheck')->setField('FieldName');
	}

	/**
	 * getOpeningBalance returns Array or String as openning balance on a perticular given
	 * date. Any transactions on that date is not taken into account.
	 * @param  MySQl_Date_String  $on_date     Date on which you want openning balance. transactions on perticular date are not included.
	 * @param  string  $side     cr/dr/both in case of both an array is returned
	 * @param  boolean $forPandL if set true only transactions from start of financial year of given date is considered, default false
	 * @return mixed  Array [CR/DR] or value based in side variable value
	 */
	function getOpeningBalance($on_date=null,$side='both',$forPandL=false) {
		if(!$on_date) $on_date = '1970-01-02';
		if(!$this->loaded()) throw $this->exception('Model Must be loaded to get opening Balance','Logic');
		

		$transaction_row=$this->add('Model_TransactionRow');
		$transaction_join=$transaction_row->join('transactions.id','transaction_id');
		$transaction_join->addField('transaction_date','created_at');
		$transaction_row->addCondition('transaction_date','<',$on_date);
		$transaction_row->addCondition('account_id',$this->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($on_date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');
		$result = $transaction_row->_dsql()->getHash();

		if($this['OpeningBalanceCr'] ==null){
			$temp_account = $this->add('Model_Account')->load($this->id);
			$this['OpeningBalanceCr'] = $temp_account['OpeningBalanceCr'];
			$this['OpeningBalanceDr'] = $temp_account['OpeningBalanceDr'];
		}

		$cr = $result['scr'];
		if(!$forPandL) $cr = $cr + $this['OpeningBalanceCr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if(!$forPandL) $dr = $dr + $this['OpeningBalanceDr'];
		if(strtolower($side) =='dr') return $dr;

		$transaction_row = null;
		unset($transaction_row);

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}

	function allDrCrSum($what, $from_date=null,$to_date=null){

		if(!$from_date) $from_date = $this->api->today;
		if(!$to_date) $to_date = $this->api->today;

		$transaction_row=$this->add('Model_TransactionRow');
		$transaction_join=$transaction_row->join('transactions.id','transaction_id');
		$transaction_join->addField('transaction_date','created_at');
		$transaction_row->addCondition('transaction_date','>=',$from_date);
		$transaction_row->addCondition('transaction_date','<',$this->api->nextDate($to_date));
		$transaction_row->addCondition('account_id',$this->id);

		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');
		$result = $transaction_row->_dsql()->getHash();

		return $result['s'.strtolower($what)];
	}

	function isMatured(){
		return $this['MaturedStatus']?true:false;
	}

	function isActive(){
		return $this['ActiveStatus']?:0;
	}

	function deActivate(){
		if(!$this->loaded())
			throw $this->exception("PLease load account before deActivate");

		if($this->isActive()){
			$this['ActiveStatus']=false;
			$this->saveAs('Account');		
		}
	}

	function lock(){
		if(!$this->loaded()) throw $this->exception('Load an Account before lock it', 'ValidityCheck')->setField('LoanAgainstAccount');
		if($this->isLocked()) throw $this->exception('Account is already Locked', 'ValidityCheck')->setField('LoanAgainstAccount');
		$this['LockingStatus'] = true;
		$this->save();
	}

	function unlock(){
		if(!$this->loaded()) throw $this->exception('Load an Account before lock it', 'ValidityCheck')->setField('LoanAgainstAccount');
		if(!$this->isLocked()) throw $this->exception('Account is Not Loacked', 'ValidityCheck')->setField('LoanAgainstAccount');
		$this['LockingStatus'] = false;
		$this->save();
	}

	function isLocked(){
		return $this['LockingStatus']==1?true:false;
	}

	function prepareDelete($revert_accounts_balances=true){
		// Delete all transactions of this account
		$transactions = $this->add('Model_Transaction');
		$tr_row_join = $transactions->join('transaction_row.transaction_id');
		$tr_row_join->addField('account_id');
		$transactions->addCondition('account_id',$this->id);

		foreach ($transactions as $transactions_array) {
			$transactions->foreceDelete();
		}
	}

	

	function addAgent($agent, $replace_existing=false){
		if($this->ref('agent_id')->loaded() and !$replace_existing)
			throw $this->exception('Account already have an agent, cannot add');

		if(($agent instanceof Model_Agent)){
			$agent = $agent->id;
		}

		$this['agent_id'] = $agent;
		$this->save();
	}

	final function daily(){
		throw $this->exception('Daily closing function must be in scheme');
	}
	final function monthly(){
		throw $this->exception('Monthly closing function must be in scheme');
	}
	final function halfYearly(){
		throw $this->exception('Half Yearly closing function must be in scheme');
	}
	final function yearly(){
		throw $this->exception('Yearly closing function must be in scheme');
	}
	
	function changeMember($member){
		if(!$this->loaded())
			throw $this->exception('Account Must be loaded to change member');

		if(!($member instanceof Model_Member) and !$member->loaded())
			throw $this->exception('Member must be passed as loaded Member Model');

		$this->add('Model_Log')->logFieldEdit('Account',$this->id,'Member',$this['member_id'],$member->id);

		$this['member_id'] = $member->id;
		$this->save();
	}

	function changeDealer($dealer){
		if(!$this->loaded())
			throw $this->exception('Account Must be loaded to change member');

		if(!($dealer instanceof Model_Dealer) and !$dealer->loaded())
			throw $this->exception('Dealer must be passed as loaded Dealer Model');

		$this->add('Model_Log')->logFieldEdit('Account',$this->id,'Dealer',$this['dealer_id'],$dealer->id);

		$this['dealer_id'] = $dealer->id;
		$this->save();
	}

	function changeAgent($agent){
		if(!$this->loaded())
			throw $this->exception('Account Must be loaded to change member');

		if(!($agent instanceof Model_Agent) and !$agent->loaded())
			throw $this->exception('Member must be passed as loaded Member Model');

		$this->add('Model_Log')->logFieldEdit('Account',$this->id,'Agent',$this['agent_id'],$agent->id);

		$this['agent_id'] = $agent->id;
		$this->save();
	}

	function swapLockingStatus(){
		$this['LockingStatus']=!$this['LockingStatus'];
		$this->save();
	}

	function swapActiveStatus(){
		$this['ActiveStatus']=!$this['ActiveStatus'];
		$this->save();
	}

	function swapMaturedStatus(){
		$this['MaturedStatus']=!$this['MaturedStatus'];
		$this->save();
	}

	function verify(){
		$this['is_verify']=true;
		$this->save();
	}

	function filter($array){
        $wq=$this->api->db->dsql()->orExpr();
        $hq=$this->api->db->dsql()->orExpr();
    
        foreach ($array as $field => $value) {
            if(is_array($value)){
            	foreach ($value as $v) {
		            $wq->where($field,'like',$v);
            	}
            }else{
	            $wq->where($field,'like',$value);
            }
        }   
        $this->addCondition($wq); 
    }

    function isRecurring(){
    	return $this['account_type'] == ACCOUNT_TYPE_RECURRING;
    }

    function isLoan(){
    	return in_array($this['account_type'],explode(",",LOAN_TYPES));
    }

    function isDDS(){
    	return $this['account_type'] == ACCOUNT_TYPE_DDS;
    }

    function isFD(){
    	return $this['account_type'] == ACCOUNT_TYPE_FIXED && $this['account_type']=='FD';
    }

    function isMIS(){
    	return $this['account_type'] == ACCOUNT_TYPE_FIXED && $this['account_type']=='MIS';
    }

	function isBank(){
    	return $this['SchemeType'] == BANK_ACCOUNTS_SCHEME;
    }

    function isCC(){
    	return $this['account_type'] == ACCOUNT_TYPE_CC;
    }

    function isSaving(){
    	return $this['account_type']=='Saving';
    }
	
	function isCurrent(){
    	return $this['account_type']=='Current';
    }


    function convert_number_to_words($number) {
	    $hyphen      = '-';
	    $conjunction = ' and ';
	    $separator   = ', ';
	    $negative    = 'negative ';
	    $decimal     = ' point ';
	    $dictionary  = array(
	        0                   => 'zero',
	        1                   => 'one',
	        2                   => 'two',
	        3                   => 'three',
	        4                   => 'four',
	        5                   => 'five',
	        6                   => 'six',
	        7                   => 'seven',
	        8                   => 'eight',
	        9                   => 'nine',
	        10                  => 'ten',
	        11                  => 'eleven',
	        12                  => 'twelve',
	        13                  => 'thirteen',
	        14                  => 'fourteen',
	        15                  => 'fifteen',
	        16                  => 'sixteen',
	        17                  => 'seventeen',
	        18                  => 'eighteen',
	        19                  => 'nineteen',
	        20                  => 'twenty',
	        30                  => 'thirty',
	        40                  => 'fourty',
	        50                  => 'fifty',
	        60                  => 'sixty',
	        70                  => 'seventy',
	        80                  => 'eighty',
	        90                  => 'ninety',
	        100                 => 'hundred',
	        1000                => 'thousand',
	        1000000             => 'million',
	        1000000000          => 'billion',
	        1000000000000       => 'trillion',
	        1000000000000000    => 'quadrillion',
	        1000000000000000000 => 'quintillion'
	    );

	    if (!is_numeric($number)) {
	        return false;
	    }

	    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
	        // overflow
	        trigger_error(
	            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
	            E_USER_WARNING
	        );
	        return false;
	    }

	    if ($number < 0) {
	        return $negative . $this->convert_number_to_words(abs($number));
	    }

	    $string = $fraction = null;

	    if (strpos($number, '.') !== false) {
	        list($number, $fraction) = explode('.', $number);
	    }

	    switch (true) {
	        case $number < 21:
	            $string = $dictionary[$number];
	            break;
	        case $number < 100:
	            $tens   = ((int) ($number / 10)) * 10;
	            $units  = $number % 10;
	            $string = $dictionary[$tens];
	            if ($units) {
	                $string .= $hyphen . $dictionary[$units];
	            }
	            break;
	        case $number < 1000:
	            $hundreds  = $number / 100;
	            $remainder = $number % 100;
	            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
	            if ($remainder) {
	                $string .= $conjunction .$this->convert_number_to_words($remainder);
	            }
	            break;
	        default:
	            $baseUnit = pow(1000, floor(log($number, 1000)));
	            $numBaseUnits = (int) ($number / $baseUnit);
	            $remainder = $number % $baseUnit;
	            $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
	            if ($remainder) {
	                $string .= $remainder < 100 ? $conjunction : $separator;
	                $string .= $this->convert_number_to_words($remainder);
	            }
	            break;
	    }

	    if (null !== $fraction && is_numeric($fraction)) {
	        $string .= $decimal;
	        $words = array();
	        foreach (str_split((string) $fraction) as $number) {
	            $words[] = $dictionary[$number];
	        }
	        $string .= implode(' ', $words);
	    }

	    return $string;
	}

}