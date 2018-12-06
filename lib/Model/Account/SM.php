<?php
class Model_Account_SM extends Model_Account_Default{
	
	public $transaction_deposit_type = TRA_SM_ACCOUNT_DEPOSIT_ENTRY;	
	public $default_transaction_deposit_narration = "Amount submited in SM Account {{AccountNumber}}";	


	function init(){
		parent::init();

		$this->addCondition('SchemeType','Default');
		$this->addCondition('scheme_name','Share Capital');
		$this->getElement('account_type')->defaultValue('SM');

		// $this->addHook('afterAccountDebited,afterAccountCredited',array($this,'closeIfPaidCompletely'));
		// $this->addHook('beforeSave',$this);
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function afterSave(){

		$member_model = $this->ref('member_id');
		$member_model['Nominee'] = $this['Nominee'];
		$member_model['NomineeAge'] = $this['NomineeAge'];
		$member_model['RelationWithNominee'] = $this['RelationWithNominee'];
		$member_model->save();

	}

	function updateForm($form){
		$form->addField('CheckBox','do_calculations');
	}

	function createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues=null,$form=null,$created_at=null){
		if($form['Amount'] < RATE_PER_SHARE || $form['Amount'] % RATE_PER_SHARE !=0) {
			throw $this->exception('Amount must be multiple of '.RATE_PER_SHARE,'ValidityCheck')->setField('Amount');
		}
		
		parent::createNewAccount($member_id,$scheme_id,$branch, $AccountNumber,$otherValues,$form,$created_at);
	}

	function deposit($amount,$narration=null,$accounts_to_debit=null,$form=null,$transaction_date=null,$in_branch=null){
		$member_id = $this['member_id']?:$form['member_id'];
		if(!$member_id) throw new \Exception("No member found to transfer share", 1);
		$this->add('Model_Share')->createNew($amount / RATE_PER_SHARE,$member_id);
		return parent::deposit($amount,$narration,$accounts_to_debit,$form,$transaction_date,$in_branch);
	}

	function getNewAccountNumber($account_type=null,$branch=null){
		
		if(!$account_type) $account_type = $this['account_type'];
		if(!$account_type) throw $this->exception('Could not Identify Account Type to generate Account Number', 'ValidityCheck')->setField('AccountNumber');
        if(!$branch) $branch= $this->api->currentBranch;

		$ac_code = $this->api->getConfig('account_code/'.$account_type,false);
		if(!$ac_code) throw $this->exception('Account type Code is not proper ')->addMoreInfo('Account account_type',$this['account_type']);

		$prefix_length = strlen($ac_code); // BRANCH CODE + SB/DDS/MIS ...

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
			// ->where('LEFT(AccountNumber,3) = "'.$branch['Code'].'"')
                        ->where('account_type',$account_type)
			->getOne();
               
        // throw new Exception($new_number, 1);
        
		return $ac_code.($new_number+1);
	}

}