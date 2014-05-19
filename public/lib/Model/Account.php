<?php
class Model_Account extends Model_Table {
	var $table= "accounts";
	public $scheme_join=null;

	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Scheme','scheme_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','loan_from_account_id')->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','account_to_debit_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','intrest_to_account_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','agent_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','LoanAgainstAccount_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Dealer','dealer_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));

		$this->hasOne('Branch','branch_id')->mandatory(true)->defaultValue(@$this->api->current_branch->id)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Staff','staff_id')->mandatory(true)->defaultValue(@$this->api->auth->model->id)->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Member','collector_id')->display(array('form'=>'autocomplete/Basic'));		
		
		//New Fields added//
		$this->addField('account_type');
		$this->addField('AccountNumber')->mandatory(true);
		$this->addField('AccountDisplayName')->caption('Account Name');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true)->system(true);

		$this->addField('ModeOfOperation')->setValueList(array('Self'=>'Self','Joint'=>'Joint'))->defaultValue('Self')->caption('Operation Mode');
		
		//New Fields added//
		$this->addField('LoanInsurranceDate')->type('datetime');
		
		$this->addField('OpeningBalanceDr')->type('money');
		$this->addField('OpeningBalanceCr')->type('money');
		$this->addField('ClosingBalance')->type('money');
		$this->addField('CurrentBalanceDr')->type('money');
		$this->addField('CurrentInterest');
		$this->addField('Nominee');
		$this->addField('NomineeAge');
		$this->addField('RelationWithNominee');
		$this->addField('MinorNomineeDOB');
		$this->addField('MinorNomineeParentName');
		$this->addField('DefaultAC')->type('boolean')->defaultValue(false);
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('CurrentBalanceCr')->type('money');
		$this->addField('LastCurrentInterestUpdatedAt')->type('datetime')->defaultValue($this->api->now);
		// $this->addField('InterestToAccount')->type('int'); now converted to hasOne Account
		$this->addField('Amount')->type('money');
		$this->addField('LockingStatus')->type('boolean')->defaultValue(false);
		$this->addField('affectsBalanceSheet')->type('boolean')->defaultValue(false);
		$this->addField('MaturedStatus')->type('boolean')->defaultValue(false);
		$this->addField('Group');
		$this->addField('PAndLGroup')->system(true);

		$this->scheme_join = $this->leftJoin('schemes','scheme_id');
		$this->scheme_join->addField('SchemeType');
		$this->scheme_join->addField('scheme_name','name');


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
		$this->hasMany('DocumentSubmitted','account_id');
		$this->hasMany('AccountGaurantor','account_id');
		$this->hasMany('TransactionRow','account_id');

		$this->addHook('beforeSave',$this);
		$this->addHook('editing',array($this,'editing_default'));


		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function editing_default(){
		$this->getElement('scheme_id')->system(true);
		$this->getElement('AccountNumber')->system(true);
	}

	function beforeSave(){

		if(!$this->loaded() AND !$this['DefaultAC'] AND strpos($this['AccountNumber'], 'SM') !==0 AND !preg_match("/[A-Z]{5}\d*$/", $this['AccountNumber'])){
			throw $this->exception('AccountNumber Format not accpeted')->addMoreInfo('acc',$this['AccountNumber']);//->setField('AccountNumber');
		}

		// PandLGroup set default
		if(!$this['Group'])
			$this['Group'] = $this->add('Model_Scheme')->load($this['scheme_id'])->get('SchemeGroup');
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

		foreach ($otherValues as $field => $value) {
			$this[$field] = $value;
		}

		$this->save();
		for($k=2;$k<=4;$k++) {
		    if($j_m_id=$form['member_ID'.$k])
		    	$this->addJointAccountMember($j_m_id);
		}
		return $this->id;
	}

	function addJointAccountMember($j_m_id){
		if(!$this->loaded()) throw $this->exception('Account Must Be loaded to add Joint Member');
		$member = $this->add('Model_Member')->load($j_m_id);
		$joint_member = $this->ref('JointMember')->addCondition('member_id',$j_m_id);
		$joint_member->tryLoadAny();
		if($joint_member->loaded())
			throw $this->exception($member['name'].' Already Joint with account '. $this['AccountNumber']);
		else
			$joint_member->save();
	}

	function updateDocument(Model_Document $document,$value){
		$document_submitted = $this->add('Model_DocumentSubmitted');
		$document_submitted->addCondition('documents_id',$document->id);
		$document_submitted->addCondition('accounts_id',$this->id);
		$document_submitted->tryLoadAny();
		
		if($value=='') throw $this->exception('Value Must Be Filled','ValidityCheck')->setField($this->api->normalizeName($document['name'].' value'));

		$document_submitted['Description'] = $value;
		$document_submitted->save();
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$transaction_date=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');
		if(!isset($this->transaction_deposit_type)) throw $this->exception('transaction_deposit_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		if(!isset($this->default_transaction_deposit_narration)) throw $this->exception('default_transaction_deposit_narration must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);

		if(!$narration) $narration = str_replace("{{AccountNumber}}", $this['AccountNumber'],str_replace('{{SchemeType}}', $this['SchemeType'], $this->default_transaction_deposit_narration));
		if(!$transaction_date) $transaction_date = $this->api->now;
		if(!$accounts_to_debit) $accounts_to_debit = array();

		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction($this->transaction_deposit_type,null,$transaction_date,$narration);
		
		$transaction->addCreditAccount($this,$amount);			

		if(count($accounts_to_debit)){
			foreach ($accounts_to_debit as $debit_info) {
				if(!is_array($debit_info)) throw $this->exception('Provided information must be array');
				foreach ($debit_info as $account => $amount) {
					$transaction->addDebitAccount($account,$amount);
				}
			}
		}else{				
			$transaction->addDebitAccount(@$this->api->current_branch['Code'].SP.CASH_ACCOUNT,$amount);
		}
		$transaction->execute();

		return $transaction->id;

	}

	function withdrawl($amount,$narration=null,$accounts_to_credit=null,$form=null,$on_date=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Withdrawing amount');
		if(!isset($this->transaction_withdraw_type)) throw $this->exception('transaction_withdraw_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		if(!isset($this->default_transaction_withdraw_narration)) throw $this->exception('default_transaction_withdraw_narration must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);

		if(!$narration) $narration = str_replace("{{AccountNumber}}", $this['AccountNumber'],str_replace("{{SchemeType}}", $this['SchemeType'], $this->default_transaction_withdraw_narration));
		if(!$on_date) $on_date = $this->api->now;
		if(!$accounts_to_debit) $accounts_to_debit = array();
		
		$transaction = $this->add('Model_Transaction');
		$transaction->createNewTransaction($this->transaction_withdraw_type,null,$on_date,$narration);
		
		$transaction->addDebitAccount($this,$amount);			

		if(count($accounts_to_credit)){
			foreach ($accounts_to_credit as $credit_info) {
				if(!is_array($credit_info)) throw $this->exception('Provided information must be array');
				foreach ($credit_info as $account => $amount) {
					$transaction->addCreditAccount($account,$amount);
				}
			}
		}else{				
			$transaction->addCreditAccount(@$this->api->current_branch['Code'].SP.CASH_ACCOUNT,$amount);
		}
		$transaction->execute();

		return $transaction->id;

	}

	/**
	 * getOpeningBalance returns Array or String as openning balance on a perticular given
	 * date. Any transactions on that date is not taken into account.
	 * @param  MySQl_Date_String  $date     Date on which you want openning balance. transactions on perticular date are not included.
	 * @param  string  $side     cr/dr/both in case of both an array is returned
	 * @param  boolean $forPandL if set true only transactions from start of financial year of given date is considered, default false
	 * @return mixed  Array [CR/DR] or value based in side variable value
	 */
	function getOpeningBalance($date=null,$side='both',$forPandL=false) {
		if(!$date) $date = '1970-01-01';
		if(!$this->loaded()) throw $this->exception('Model Must be loaded to get opening Balance','Logic');
		

		$transaction_row=$this->add('Model_TransactionRow');
		$transaction_join=$transaction_row->join('transactions.id','transaction_id');
		$transaction_row->addCondition('created_at','<',$date);
		$transaction_row->addCondition('account_id',$this->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		$transaction_row->_dsql()->del('fields')->field('SUM(amountDr)')->field('SUM(amountCr)');
		$result = $transaction_row->_dsql()->getHash();

		$cr = $result['SUM(amountCr)'];
		if(!$forPandL) $cr = $cr + $this['OpeningBalanceCr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['SUM(amountDr)'];		
		if(!$forPandL) $dr = $dr + $this['OpeningBalanceDr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr);
	}

	function isMatured(){
		return $this['MaturedStatus']?:0;
	}

	function isActive(){
		return $this['ActiveStatus']?:0;
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

	function delete($forced =false){
		if($forced){
			$this->prepareDelete(true);
		}
		parent::delete();
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
	
}