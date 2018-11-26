<?php


class Model_ContentFile extends Model_Table {
	public $table = "content_files";
	public $file_path = 'templates/contents/';

	public $works_on='Account'; // or Member
	
	function init(){
		parent::init();

		$this->addField('name');
		// $this->addField('caption');
		$this->addField('account_type')->enum(array_merge(explode(",", ACCOUNT_TYPES),['Base']));

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
	}

	function beforeSave(){
		$this['name'] = $this->app->normalizeName($this['name']);
		if(!file_exists($this->getPath())){
			touch($this->getPath());
		}
	}

	function beforeDelete(){
		if(file_exists($this->getPath())){
			unlink($this->getPath());
		}
	}

	function getPath(){
		return $this->file_path.'/'.$this['name'];
	}

	function getContent(){
		return file_get_contents($this->getPath());
	}

	function getAccountModel($id=null){
		$type="";
		if($this['account_type'] && $this['account_type'] != 'Base' ){
			$type='_'.$this['account_type'];
		}

		$member_model = $this->add('Model_Member');
		$member_fields = $member_model->getActualFields();
		
		$model = $this->add('Model_Account'.$type);

		$model->addHook('afterLoad',function($m){
			foreach ($m->getActualFields() as $fld) {
				if($m->getElement($fld)->type() == 'boolean'){
					$m[$fld] = $m[$fld]?"Yes":"No";
				}
			}
		});

		$model->addExpression('logo')->set(function($m,$q){
			return '"<img src=\"templates/images/logo.jpg\" style=\"width:100%; max-width:100%\"/>"';
		});

		$model->addExpression('logo_src')->set(function($m,$q){
			return "'templates/images/logo.jpg'";
		});

		foreach ($member_fields as $mf) {
			if(in_array($mf, ['id'])) continue;
			$model->addExpression('member_'.$mf)->set(function($m,$q)use($mf){
				return $m->refSQL('member_id')->fieldQuery($mf);
			})->type($member_model->getElement($mf)->type());
		}

		foreach ($member_fields as $mf) {
			if(in_array($mf, ['id'])) continue;

			$model->addExpression('guarantor_'.$mf)->set(function($m,$q)use($mf){
				$gm = $this->add('Model_Member',['table_alias'=>'gm']);
				$ag_j = $gm->leftJoin('account_guarantors.member_id');
				$ag_j->addField('account_id');
				$gm->addCondition('account_id',$m->getField('id'));
				$gm->setOrder('created_at','desc');
				$gm->setLimit(1);
				return $gm->fieldQuery($mf);
			})->type($member_model->getElement($mf)->type());
		}

		$documents = $this->add('Model_DocumentAll');
		foreach ($documents as $dci=>$obj) {
			$model->addExpression($this->app->normalizeName($obj['name']))->set(function($m,$q)use($dci){
				return $m->refSQL('DocumentSubmitted')
						->addCondition('documents_id',$dci)
						->addCondition('accounts_id',$m->getField('id'))
						->fieldQuery('Description');
			});
		}

		if($id){

			$model->tryLoadBy('AccountNumber',$id);
			if(!$model->loaded()){
				$model->tryLoad($id);
			}
			if(!$model->loaded()){
				throw new \Exception("AccountNumber ". $id. ' Not found'. ($type?' Under '.$type:''), 1);
			}
		} 

		return $model;

	}

}