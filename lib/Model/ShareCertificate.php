<?php

class Model_ShareCertificate extends Model_Table {
	public $table = 'share_certificate';

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('status')->enum(['Issued','Submitted'])->defaultValue('Issued');

		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('submitted_at')->type('datetime')->defaultValue($this->app->now);

		$this->addExpression('start_share')->set(function($m,$q){
			return $m->add('Model_Share')->addCondition('share_certificate_id',$q->getField('id'))
					->_dsql()->del('fields')->field($q->expr('MIN(no)'));
		});

		$this->addExpression('end_share')->set(function($m,$q){
			return $m->add('Model_Share')->addCondition('share_certificate_id',$q->getField('id'))
					->_dsql()->del('fields')->field($q->expr('MAX(no)'));
		});

		$this->addExpression('share_count')->set(function($m,$q){
			return $m->add('Model_Share')->addCondition('share_certificate_id',$q->getField('id'))
				->count();
		});

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