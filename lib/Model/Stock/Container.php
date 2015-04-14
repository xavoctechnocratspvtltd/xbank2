<?php

class Model_Stock_Container extends Model_Table {
	var $table= "stock_containers";
	function init(){
		parent::init();

		$this->hasOne('Branch','branch_id')->sortable(true);
		$this->addCondition('branch_id',$this->api->current_branch->id);
		
		$this->addField('name')->mandatory(true)->sortable(true);
		
		$this->hasMany('Stock_Row','container_id');
		$this->hasMany('Stock_ContainerRowItemQty','container_id');
		$this->hasMany('Stock_Container','from_container_id',null,'FromContainer');
		$this->hasMany('Stock_Container','to_container_id',null,'ToContainer');
		
		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
		
		$this->add('dynamic_model/Controller_AutoCreator');

	}

	function beforeSave(){
		$tmp = $this->add('Model_Stock_Container');
		$tmp->addCondition('name',$this['name']);
		$tmp->addCondition('branch_id',$this['branch_id']);

		if($this->loaded()){
			$tmp->addCondition('id','<>',$this->id);
		}

		$tmp->tryLoadAny();
		if($tmp->loaded())
			throw $this->exception('Name Already Exists','ValidityCheck')->setField('name');
	}

	function createNew($name,$other_fields=array(),$form=null){

		if($this->loaded())
			throw $this->exception('Please call on loaded Object');
		$this['name']=$name;
		$this['branch_id']=$this->api->currentBranch->id;
		$this->save();
	}


	function beforeDelete(){
		if($this['name']=='General' or $this['name']=='Dead' or $this['name'] =='UsedDefault'){
			$this->api->js()->univ()->errorMessage('System Generated, Cannot Delete')->execute();
		}

		if( $this->ref('Stock_Row')->count()->getOne() > 0 )
			$this->api->js()->univ()->errorMessage(''.$this['name'].' Contains Row(s), Cannot Delete')->execute();
			
	}
	
	function remove(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the record to be delete');
		$this->delete();
	}

	function addRow($name){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the container');
		$row=$this->add('Model_Row');
		$row->createNew($name,$this);			
	}

	function removeRow($row){
		if(!$this->loaded())
			throw $this->exception('Unable To determine the container, Please Specify');
		if(!$row->loaded())
			throw $this->exception('Unable To determine the Item To be removed');
		if($r=$this->hasRow($row))
			$r->remove();

	}

	function hasRow($row){
		if(!$row->loaded())
			throw $this->exception('Please pass loaded object of Row');
		if(!$this->loaded())
			throw $this->exception('Please call on  loaded object');
		return $row->isExist($this);

	}
	 
	function allRows(){
		if(!$this->loaded())
			throw $this->exception('Unable To determine Container, Please Specify');
		return $this->ref('Stock_Row');
	}	

	function loadUsedDefaultContainer($branch_id=null){
		$this->_dsql()->del('where');
		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;

		$this->addCondition('branch_id',$branch_id);
		$this->addCondition('name','UsedDefault');
		$this->tryLoadAny();
		if($this->loaded()){
			return $this;	
		}
			return false;	
				
	}

	function loadGeneralContainer($branch_id=null){
		
		$this->_dsql()->del('where');

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;
			
		$this->addCondition('branch_id',$branch_id);
		$this->addCondition('name','General');
		$this->tryLoadAny();
		if(!$this->loaded()){
			// throw new Exception($branch_id);
			return false;	
		}
		return $this;	
				
	}

	function loadGeneralAndUsed($branch_id=null){	
		$this->_dsql()->del('where');

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;
			
		$this->addCondition('branch_id',$branch_id);
		$this->addCondition('name',array('General','UsedDefault'));
		$this->tryLoadAny();
		if(!$this->loaded()){
			return false;	
		}
		return $this;	
				
	}

	function loadDeadContainer($branch_id=null){
		$this->_dsql()->del('where');

		if(!$branch_id)
			$branch_id = $this->api->current_branch->id;
		$this->addCondition('branch_id',$branch_id);
		$this->addCondition('name','Dead');
		$this->tryLoadAny();
		return $this;
	}

	function loadContainer($container_id){
		
		$this->addCondition('branch_id',$this->api->current_branch->id);
		$this->addCondition('id',$container_id);
		$this->tryLoadAny();
		if($this->loaded()){
			return $this;
		}else 
			throw $this->exception("Container( ".$container_id.") not Exits");			
							
	}
	
}