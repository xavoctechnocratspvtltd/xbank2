<?php
class Model_Account extends Model_Table {
	var $table= "accounts";

	function init(){
		parent::init();

		$this->hasOne('Agent','agent_id')->display(array('form'=>'authcomplete/Basic'));
		$this->hasOne('Branch','branch_id')->defaultValue($this->api->current_branch->id);
		$this->hasOne('Staff','staff_id')->defaultValue($this->api->auth->model->id);
		$this->hasOne('Dealer','dealer_id')->display(array('form'=>'authcomplete/Basic'));
		$this->hasOne('Member','collector_id')->display(array('form'=>'authcomplete/Basic'));
		$this->hasOne('Member','member_id')->display(array('form'=>'authcomplete/Basic'));
		$this->hasOne('Scheme','scheme_id')->display(array('form'=>'authcomplete/Basic'));
		
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
		$this->addField('AccountDisplayName');
		$this->addField('PAndLGroup');

		$this->leftJoin('schemes','scheme_id')
			->addField('SchemeType');

		$this->addExpression('name')->set('AccountNumber');


		$this->hasMany('Jointmember','account_id');
		$this->hasMany('Premium','account_id');

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

	function manageForm($form){
		$this->hook('accountFormCreated',array($form));
		if($form->isSubmitted()){
			$this->hook('accountFormSubmitted',array($form));
			$values = $form->getAllFields();
			$this->createNewAccount($values['member_id'],$values['scheme_id'],$values['AccountNumber'],$values);
			$form->js()->univ()->successMessage('HI')->execute();
		}
	}

	function createNewAccount($member_id,$scheme_id,$branch_id, $AccountNumber,$otherValues=array()){
		$this['member_id'] = $member_id;
		$this['scheme_id'] = $scheme_id;
		$this['AccountNumber'] = $AccountNumber;
		$this['branch_id'] = $branch_id;

		foreach ($otherValues as $field => $value) {
			$this[$field] = $value;
		}
		$this->save();
	}

	function deposit($amount){
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