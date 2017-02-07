<?php

class Grid_Scheme extends Grid{

	public $head=null;

	public $vp;

	function init(){
		parent::init();
		// $this->vp = $this->add('VirtualPage')->set(function($p){
		// 	$p->add('Grid_Account')->setModel($p->add('Model_Scheme')->load($_GET['scheme_id'])->accounts());
		// });
	}

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		$this->model->setOrder('created_at','desc');
		
		if($this->hasColumn('PremiumMode'))
			$this->addFormatter('PremiumMode','PremiumMode');
		// $this->addFormatter('CollectorCommissionRate','wrap');

		$this->addColumn('text','limits');

		$this->removeColumn('ReducingOrFlatRate');
		$this->removeColumn('ActiveStatus');
		$this->removeColumn('NumberOfPremiums');
		$this->removeColumn('balance_sheet_id');
		$this->removeColumn('MinLimit');
		$this->removeColumn('MaxLimit');
		$this->removeColumn('ProcessingFeesinPercent');
		$this->removeColumn('SchemeGroup');
		$this->removeColumn('SchemePoints');
		$this->removeColumn('InterestToAnotherAccount');
	}

	function formatRow(){

		if($this->head==null){
			$this->head = $this->model['balance_sheet_id'];
		}

		if($this->head != $this->model['balance_sheet_id']){
			$this->setTDParam('balance_sheet','style/color','red');
			$this->head = $this->model['balance_sheet_id'];
		}else{
			$this->setTDParam('balance_sheet','style/color','');
		}

		if(!$this->model['ActiveStatus'] OR ($this->model['valid_till'] AND strtotime($this->model['valid_till']) < strtotime($this->app->today)) ){
			$this->setTDParam('name','style/color','red');
			$this->setTDParam('name','style/text-decoration','line-through');
			$this->setTDParam('valid_till','style/color','red');
			$this->setTDParam('valid_till','style/text-decoration','line-through');
		}
		else{
			$this->setTDParam('name','style/color','');
			$this->setTDParam('valid_till','style/color','');
		}


		$this->current_row['limits']= 'Min ' .$this->model['MinLimit'] . ' /- ' . ($this->model['MaxLimit'] !=-1? ' to ' . $this->model['MaxLimit'] .' /-': '');
		$this->current_row_html['Interest'] = $this->model['Interest'] . '% <small><small class="atk-text-dimmed">'. $this->model['ReducingOrFlatRate'] .'</small></span>';
		$this->current_row_html['ProcessingFees']= $this->current_row['ProcessingFees'] . ' '. ($this->model['ProcessingFeesinPercent']? ' %': ' /-');

		// $this->current_row_html['total_active_accounts'] = '<a onclick="javascript: $.univ().frameURL(\'Scheme Accounts\',\''.$this->api->url($this->vp->getURL(),array('scheme_id'=>$this->model->id)).'\')">'. $this->current_row['total_active_accounts'] .'</a>';

		parent::formatRow();
	}

	function format_PremiumMode($field){
		$this->current_row_html[$field]= $this->current_row['PremiumMode'] . ' ['.$this->current_row['NumberOfPremiums'].']' ;
	}
}