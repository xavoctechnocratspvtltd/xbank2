<?php
class Model_Account extends Model_Table {
	var $table= "accounts";
	public $scheme_join=null;
	public $allow_any_name = false;
	
	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Scheme','scheme_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','loan_from_account_id')->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','account_to_debit_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','related_account_id')->system(true);
		$this->hasOne('Account','intrest_to_account_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','MaturityToAccount_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','agent_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','LoanAgainstAccount_id')->display(array('form'=>'autocomplete/Basic'))->defaultValue('0');
		$this->hasOne('Dealer','dealer_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));

		$this->hasOne('Branch','branch_id')->mandatory(true)->defaultValue(@$this->api->current_branch->id)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Staff','staff_id')->mandatory(true)->defaultValue(@$this->api->auth->model->id)->display(array('form'=>'autocomplete/Basic'));
		
		$this->hasOne('Mo','mo_id')->display(array('form'=>'autocomplete/Basic'))->defaultValue(0);
		$this->hasOne('Team','team_id')->display(array('form'=>'autocomplete/Basic'))->defaultValue(0);
		// $this->hasOne('Member','collector_id')->display(array('form'=>'autocomplete/Basic'));		
		
		//New Fields added//
		$this->addField('account_type');
		$this->addField('AccountNumber');//->mandatory(true);
		$this->addField('AccountDisplayName')->caption('Account Displ. Name');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true)->system(true);

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
		$this->addField('RelationWithNominee')->enum(array('Father','Mother','Husband','Wife','Brother','Sister','Son','Daughter'));
		$this->addField('MinorNomineeDOB');
		$this->addField('MinorNomineeParentName');
		$this->addField('DefaultAC')->type('boolean')->defaultValue(false);
		$this->addField('created_at')->type('datetime')->sortable(true);//->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime');//->defaultValue($this->api->now);
		$this->addField('CurrentBalanceCr')->type('money');
		$this->addField('LastCurrentInterestUpdatedAt')->type('datetime');//->defaultValue($this->api->now);
		// $this->addField('InterestToAccount')->type('int'); now converted to hasOne Account
		$this->addField('Amount')->type('money')->defaultValue(0);
		$this->addField('LockingStatus')->type('boolean')->defaultValue(false);
		$this->addField('affectsBalanceSheet')->type('boolean')->defaultValue(false);
		$this->addField('MaturedStatus')->type('boolean')->defaultValue(false);
		$this->addField('Group');
		$this->addField('PAndLGroup')->system(true);
		
		$this->addField('extra_info')->type('text')->system(true); // Put json style extra info in this field

		$this->scheme_join = $this->leftJoin('schemes','scheme_id');
		$this->scheme_join->addField('SchemeType');
		$this->scheme_join->addField('scheme_name','name');

		$this->add('filestore/Field_Image','doc_image_id')->type('image')->mandatory(true);
		$this->add('filestore/Field_Image','sig_image_id')->type('image')->mandatory(true);

		$this->addExpression('branch_code')->set(function($m,$q){
			return $m->refSQL('branch_id')->fieldQuery('Code');
		});

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
		$this->hasMany('Comment','account_id');

		$this->addHook('beforeSave',array($this,'defaultBeforeSave'));
		$this->addHook('editing',array($this,'editing_default'));


		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function editing_default(){
		$this->getElement('scheme_id')->system(true);
		$this->getElement('AccountNumber')->system(true);
		$this->getElement('account_type')->system(true);
		$this->getElement('Amount')->system(true);
		$this->getElement('ModeOfOperation')->system(true);
	}

	function defaultBeforeSave(){

		if(!$this->loaded() AND !$this['DefaultAC'] AND strpos($this['AccountNumber'], 'SM') !==0 AND !preg_match("/[A-Z]{5}\d*$/", $this['AccountNumber']) AND !@$this->allow_any_name ){
			throw $this->exception('AccountNumber Format not accpeted')->addMoreInfo('acc',$this['AccountNumber']);//->setField('AccountNumber');
		}


		// PandLGroup set default
		if(!$this['Group'])
			$this['Group'] = $this->add('Model_Scheme')->load($this['scheme_id'])->get('SchemeGroup');
		if(!$this['PAndLGroup'])
			$this['PAndLGroup'] = $this['Group'];
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
	}

	function creditOnly($amount){
		$this->hook('beforeAccountCredited',array($amount));
		$this['CurrentBalanceCr']=$this['CurrentBalanceCr']+$amount;
		$this->save();
		$this->hook('afterAccountCredited',array($amount));
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
		$pending_account['AccountNumber'] = 'new_account';
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
		
		if($value=='') throw $this->exception('Value Must Be Filled','ValidityCheck')->setField($this->api->normalizeName($document['name'].' value'));

		$document_submitted['Description'] = $value;
		$document_submitted->save();
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');
		if(!isset($this->transaction_deposit_type)) throw $this->exception('transaction_deposit_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		if(!isset($this->default_transaction_deposit_narration)) throw $this->exception('default_transaction_deposit_narration must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);

		if(!$narration) $narration = str_replace("{{AccountNumber}}", $this['AccountNumber'],str_replace('{{SchemeType}}', $this['SchemeType'], $this->default_transaction_deposit_narration));
		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$accounts_to_debit) $accounts_to_debit = array();
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction($this->transaction_deposit_type,$in_branch,$transaction_date,$narration);
		
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

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null,$in_branch=null,$reference_account_id=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Withdrawing amount');
		if(!isset($this->transaction_withdraw_type)) throw $this->exception('transaction_withdraw_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		if(!isset($this->default_transaction_withdraw_narration)) throw $this->exception('default_transaction_withdraw_narration must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);

		if(!$narration) $narration = str_replace("{{AccountNumber}}", $this['AccountNumber'],str_replace("{{SchemeType}}", $this['SchemeType'], $this->default_transaction_withdraw_narration));
		if(!$on_date) $on_date = $this->api->now;
		if(!$accounts_to_credit OR !is_array($accounts_to_credit)) $accounts_to_credit = array();
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction($this->transaction_withdraw_type,$in_branch,$on_date,$narration,null,array('reference_account_id'=>$reference_account_id));
		
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



	function conveyance($staff,$amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'CONVEYANCE EXPENSES');
		$staff_model=$this->add('Model_Staff')->load($staff);
		$narration.="-".$staff_model['name'];
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_account_id'=>$this->id));
		$transaction->createNewTransaction(TRA_CONVEYANCE_CAHRGES,$in_branch,$transaction_date,$narration,null,array('reference_account_id'=>$staff));
		
		$transaction->addDebitAccount($account_dr,$amount);
		$transaction->addCreditAccount($account_cr,$amount);			

		$transaction->execute();

		return $transaction->id;

	}


	function fuel($amount,$narration=null,$amount_from_account,$form=null,$transaction_date=null,$in_branch=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');

		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$in_branch) $in_branch = $this->api->current_branch;

		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'FUEL EXPENSES');
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_account_id'=>$this->id));
		$transaction->createNewTransaction(TRA_FUEL_CAHRGES,$in_branch,$transaction_date,$narration);
		
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
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_account_id'=>$this->id));
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

		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'LEGAL EXPENSES RECEIVED');
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_account_id'=>$this->id));
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

		$account_cr = $this->add('Model_Account')
										->loadBy('AccountNumber',$amount_from_account);
		$account_dr = $this->add('Model_Account')
										->loadBy('AccountNumber',$this->api->currentBranch['Code'].SP.'Visit Charge');
		$transaction = $this->add('Model_Transaction');
		// ---- $transaction->createNewTransaction(transaction_type, $branch, $transaction_date, $Narration, $only_transaction, array('reference_account_id'=>$this->id));
		$transaction->createNewTransaction(TRA_VISIT_CHARGE,$in_branch,$transaction_date,$narration);
		
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

		$cr = $result['scr'];
		if(!$forPandL) $cr = $cr + $this['OpeningBalanceCr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if(!$forPandL) $dr = $dr + $this['OpeningBalanceDr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}

	function isMatured(){
		return $this['MaturedStatus']?true:false;
	}

	function isActive(){
		return $this['ActiveStatus']?:0;
	}

	function lock(){
		if(!$this->loaded()) throw $this->exception('Load an Account before lock it', 'ValidityCheck')->setField('LoanAgainstAccount');
		if($this->isLocked()) throw $this->exception('Account is already Loacked', 'ValidityCheck')->setField('LoanAgainstAccount');
		$this['LockingStatus'] = true;
		$this->save();
	}

	function isLocked(){
		return $this['LockingStatus']?true:false;
	}

	function prepareDelete($revert_accounts_balances=true){
		// Delete all transactions of this account
		$transactions = $this->add('Model_Transaction');
		$tr_row_join = $transactions->join('transaction_row.transaction_id');
		$tr_row_join->addField('account_id');
		$transactions->addCondition('account_id',$this->id);

		foreach ($transactions as $transactions_array) {
			$transactions->delete(true);
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

	function changeDealer($member){
		throw $this->exception(' Exception text', 'ValidityCheck')->setField('FieldName');

	}

	function swapLockingStatus(){
		$this['LockingStatus']=!$this['LockingStatus'];
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
}