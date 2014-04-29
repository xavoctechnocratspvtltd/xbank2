<?php
class Model_Account extends Model_Table {
	var $table= "accounts";
	function init(){
		parent::init();

		$this->hasOne('Agent','agent_id');
		$this->hasOne('Branch','branch_id');
		$this->hasOne('Staff','staff_id');
		$this->hasOne('Dealer','dealer_id');
		$this->hasOne('Collector','collector_id');
		$this->hasOne('Member','member_id');
		$this->hasOne('Scheme','scheme_id');
		
		$this->addField('OpeningBalanceDr')->type('money');
		$this->addField('OpeningBalanceCr')->type('money');
		$this->addField('ClosingBalance')->type('money');
		$this->addField('CurrentBalanceDr')->type('money');
		$this->addField('CurrentInterest');
		$this->addField('ActiveStatus')->type('boolean')->defaultValue(true);
		$this->addField('Nominee');
		$this->addField('NomineeAge');
		$this->addField('RelationWithNominee');
		$this->addField('MinorNomineeDOB');
		$this->addField('MinorNomineeParentName');
		$this->addField('ModeOfOperation');
		$this->addField('DefaultAC')->type('boolean')->defaultValue(false);
		$this->addField('AccountNumber');
		$this->addField('created_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('updated_at')->type('datetime')->defaultValue($this->api->now);
		$this->addField('CurrentBalanceCr')->type('money');
		$this->addField('LastCurrentInterestUpdatedAt')->type('datetime')->defaultValue($this->api->now);
		$this->addField('InterestToAccount')->type('int');
		$this->addField('RdAmount')->type('money');
		$this->addField('LoanInsurranceDate')->type('datetime')->defaultValue($this->api->now);
		$this->addField('LockingStatus')->type('boolean')->defaultValue(false);
		$this->addField('LoanAgainstAccount')->type('int');
		$this->addField('affectsBalanceSheet')->type('boolean')->defaultValue(false);
		$this->addField('MaturedStatus')->type('boolean')->defaultValue(false);
		$this->addField('LockingStatus')->type('boolean')->defaultValue(false);
		$this->addField('AccountDisplayName');
		$this->addField('PAndLGroup');

		$this->hasMany('Jointmember','account_id');
		$this->hasMany('Premium','account_id');

		//$this->add('dynamic_model/Controller_AutoCreator');
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
		
		$this->hook('beforeAccountDebited',array($amount));
		$this['CurrentBalanceDr']=$this['CurrentBalanceDr']+$amount;
		$this->save();
		$this->hook('afterAccountDebited',array($amount));
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
		$this->hook('beforeAccountCredited',array($amount));
		$this['CurrentBalanceCr']=$this['CurrentBalanceCr']+$amount;
		$this->save();
		$this->hook('afterAccountCredited',array($amount));
	}

	function deposit($amount,$from_account=CASH_ACCOUNT){

	}

	function withdrawl($amount,$to_account=CASH_ACCOUNT){

	}
}