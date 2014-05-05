<?php
class Model_Account extends Model_Table {
	var $table= "accounts";

	function init(){
		parent::init();

		$this->hasOne('Member','member_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Scheme','scheme_id')->mandatory(true)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','loan_from_account_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','account_to_debit_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','intrest_to_account_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Agent','agent_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','LoanAgainstAccount_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Dealer','dealer_id')->display(array('form'=>'autocomplete/Basic'));


		$this->hasOne('Branch','branch_id')->mandatory(true)->defaultValue($this->api->current_branch->id)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Staff','staff_id')->mandatory(true)->defaultValue($this->api->auth->model->id)->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Member','collector_id')->display(array('form'=>'autocomplete/Basic'));
		

		
		
		//New Fields added//
		$this->addField('AccountNumber')->mandatory(true);
		$this->addField('AccountDisplayName')->caption('Account Name');
		$this->addField('gaurantor');
		$this->addField('gaurantorAddress');
		$this->addField('gaurantorPhNo');
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
		$this->addField('InterestToAccount')->type('int');
		$this->addField('Amount')->type('money');
		$this->addField('LockingStatus')->type('boolean')->defaultValue(false);
		$this->addField('affectsBalanceSheet')->type('boolean')->defaultValue(false);
		$this->addField('MaturedStatus')->type('boolean')->defaultValue(false);
		$this->addField('PAndLGroup');

		$this->leftJoin('schemes','scheme_id')
			->addField('SchemeType');


		$this->addExpression('name')->set(function($m,$q){
			
			$member = $m->add('Model_Member',array('table_alias'=>'account_holder'));
			$member->addCondition('id',$q->getField('member_id'));
			$member->_dsql()->del('fields')->field('name');

			$member_father = $m->add('Model_Member',array('table_alias'=>'account_holder_father'));
			$member_father->addCondition('id',$q->getField('member_id'));
			$member_father->_dsql()->del('fields')->field('FatherName');


			return '(CONCAT(
								('.$member->_dsql()->render().'),
								" [ ",
								('.$member_father->_dsql()->render().'),
								" ] ",
								'. $q->getField('AccountNumber') .',
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

		$this->addHook('beforeSave',$this);


		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		// PandLGroup set default
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

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array(),$form=null){
		$this['member_id'] = $member_id;
		$this['scheme_id'] = $scheme_id;
		$this['AccountNumber'] = $this->api->current_branch['Code'].$AccountNumber;
		$this['branch_id'] = $branch_id;

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

	function deposit($amount,$narration,$accounts_to_debit=array(),$form=null){
		if(!$this->loaded()) throw $this->exception('Account must be loaded before Depositing amount');
		if(!isset($this->transaction_deposit_type)) throw $this->exception('transaction_deposit_type must be defined for this account type')->addMoreInfo('AccountType',$this['SchemeType']);
		
		// Check if account belongs to currentBranch ...
		// If yes then ok otherwise do interbranch entry
		if($this['branch_id'] == $this->api->current_branch->id){
			// Account Belongs to same branch
			$cr_array=array($this->api->current_branch['Code'].SP.$this['AccountNumber']=>$amount);

			if(!count($accounts_to_debit))
				$accounts_to_debit=array($this->api->current_branch['Code'].SP.CASH_ACCOUNT=>$amount);

			$transaction = $this->add('Model_Transaction');
			$transaction->doTransaction($accounts_to_debit, $cr_array, $this->transaction_deposit_type);

		}else{
			// Account belongs to another branch
			// Perform interbranch transaction
			
		}
		throw $this->exception ('Must Re Declare in Account Sub Class');
	}

	function withdrawl($amount){
		throw $this->exception ('Must Re Declare in Account Sub Class');

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