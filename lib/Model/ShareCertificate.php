<?php

class Model_ShareCertificate extends Model_Table {
	public $table = 'share_certificate';

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('status')->enum(['Issued','Submitted'])->defaultValue('Issued');

		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('submitted_at')->type('datetime')->defaultValue($this->app->now);

		$this->hasMany('Share','share_certificate_id');
	}

	function createNew(){
		$max_name = $this->add('Model_ShareCertificate')
				->_dsql()
				->del('field')
				->field('MAX(name)')
				->getOne();

		return $this->add('Model_ShareCertificate')
			->set('name',$max_name+1)
			->save();
	}


}