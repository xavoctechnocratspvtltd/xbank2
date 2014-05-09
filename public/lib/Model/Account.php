<?php
class Model_Account extends Model_Table {
	var $table= "accounts";

	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Scheme','scheme_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','loan_from_account_id')->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Account','account_to_debit_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','intrest_to_account_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','agent_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','LoanAgainstAccount_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Dealer','dealer_id')->display(array('form'=>'autocomplete/Basic'));

		$this->hasOne('Branch','branch_id')->mandatory(true)->defaultValue(@$this->api->current_branch->id)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Staff','staff_id')->mandatory(true)->defaultValue(@$this->api->auth->model->id)->display(array('form'=>'autocomplete/Basic'));
		// $this->hasOne('Member','collector_id')->display(array('form'=>'autocomplete/Basic'));		
		
		//New Fields added//
		$this->addField('AccountNumber')->mandatory(true);
		$this->addField('AccountDisplayName')->caption('Account Name');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true)->system(true);

		$this->addField('ModeOfOperation')->setValueList(array('Self'=>'Self','Joint'=>'Joint'))->defaultValue('Self')->caption('Operation Mode');
		
		//New Fields added//
		$this->addField('LoanInsurranceDate')->type('datetime')->defaultValue($this->api->now);
		
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

		$this->leftJoin('schemes','scheme_id')
			->addField('SchemeType');


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
								IFNULL('.$q->getField('AccountDisplayName').',AccountNumber)
							)
					)';
		});

		// $member_join = $this->leftJoin('members','member_id');
		// $member_join->addField('member_name','name');
		// $member_join->addField('FatherName');

		// $this->debug();

		$this->hasMany('Jointmember','account_id');
		$this->hasMany('Premium','account_id');
		$this->hasMany('DocumentSubmitted','account_id');
		$this->hasMany('AccountGaurantor','account_id');

		$this->addHook('beforeSave',$this);


		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		// PandLGroup set default
		$this['Group'] = $this->ref('scheme_id')->get('SchemeGroup');
		
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

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=array(),$form=null){
		if(!($branch instanceof Model_Branch) or !$branch->loaded()) throw $this->exception('Branch Muct be Loaded Object of Model_Branch');
		
		$this['member_id'] = $member_id;
		$this['scheme_id'] = $scheme_id;
		$this['AccountNumber'] = $AccountNumber;
		$this['branch_id'] = $branch->id;

		foreach ($otherValues as $field => $value) {
			$this[$field] = $value;
		}
		$this->save();
		return $this->id;
	}

	function updateDocument(Model_Document $document,$value){
		$document_submitted = $this->add('Model_DocumentSubmitted');
		$document_submitted->addCondition('documents_id',$document->id);
		$document_submitted->addCondition('accounts_id',$this->id);
		$document_submitted->tryLoadAny();
		
		$document_submitted['Description'] = $value;
		$document_submitted->save();
	}

	function deposit($amount,$narration=null,$accounts_to_debit=array(),$form=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');
		if(!isset($this->transaction_deposit_type)) throw $this->exception('transaction_deposit_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		if(!isset($this->default_transaction_deposit_narration)) throw $this->exception('default_transaction_deposit_narration must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);

		if(!$narration) $narration = $this->default_transaction_deposit_narration;
		
		// Check if account belongs to currentBranch ...
		// If yes then ok otherwise do interbranch entry
		if($this['branch_id'] == $this->api->current_branch->id){
			// Account Belongs to same branch
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewTransaction($this->transaction_deposit_type,null,null,$narration);
			
			$transaction->addCreditAccount($this['AccountNumber'],$amount);			

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
		}else{
			// Account belongs to another branch
			// Perform interbranch transaction
			
			$transaction = $this->add('Model_Transaction');
			$transaction->createNewInterBranchTransaction($this->ref('branch_id'),$this->transaction_deposit_type);

			if(count($accounts_to_debit)){
				foreach ($accounts_to_debit as $debit_info) {
					if(!is_array($debit_info)) throw $this->exception('Provided information must be array');
					foreach ($debit_info as $account => $amount) {
						$transaction->addMyAccount($account,$amount,'dr');
					}
				}
			}else{
				$transaction->addMyAccount(@$this->api->current_branch['Code'].SP.CASH_ACCOUNT,$amount,'dr');
			}

			$transaction->addOtherAccount($this['AccountNumber'],$amount,'cr');

			$transaction->execute();

		}
	}

	function withdrawl($amount){
		throw $this->exception ('Must Re Declare in Account Sub Class');

	}

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

	final function daily(){
		$this->exception('Daily closing function must be in scheme');
	}
	final function monthly(){
		$this->exception('Monthly closing function must be in scheme');
	}
	final function halfYearly(){
		$this->exception('Half Yearly closing function must be in scheme');
	}
	final function yearly(){
		$this->exception('Yearly closing function must be in scheme');
	}
	
}