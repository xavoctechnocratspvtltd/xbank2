<?php
class Model_DocumentSubmitted extends Model_Table {
	var $table= "documents_submitted";
	function init(){
		parent::init();

		$this->hasOne('Document','documents_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Account','accounts_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('Member','member_id')->display(array('form'=>'Member'));
		$this->hasOne('Agent','agent_id')->display(array('form'=>'autocomplete/Basic'));
		//NEW ADDED
		$this->hasOne('Dealer','dealer_id')->display(array('form'=>'autocomplete/Basic'));
		//NEW ADDED
		$this->hasOne('AgentGuarantor','agentguarantor_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('DSA','dsa_id')->display(array('form'=>'autocomplete/Basic'));
		$this->hasOne('DSAGuarantor','dsaguarantor_id')->display(array('form'=>'autocomplete/Basic'));

		$this->addField('Description');

		$this->add('filestore/Field_Image','doc_image_id')->type('image');//->display(array('grid'=>'picture'));//->mandatory(true);
		
		$this->addField('submitted_on')->type('date')->defaultValue($this->api->today);

		$this->addHook('beforeSave',$this);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if(!$this->loaded()){
			$check_old = $this->add('Model_DocumentSubmitted');
			$check_old->addCondition('documents_id',$this['documents_id']);
			$check_old->addCondition('accounts_id',$this['accounts_id']);
			$check_old->addCondition('member_id',$this['member_id']);
			$check_old->addCondition('agent_id',$this['agent_id']);
			$check_old->addCondition('agentguarantor_id',$this['agentguarantor_id']);
			$check_old->addCondition('dsa_id',$this['dsa_id']);
			$check_old->addCondition('dsaguarantor_id',$this['dsaguarantor_id']);

			$check_old->tryLoadAny();

			if($check_old->loaded()){
				throw $this->exception('Entry Already Exists, Try to edit it', 'ValidityCheck')->setField('Description');
			}

		}
	}

}