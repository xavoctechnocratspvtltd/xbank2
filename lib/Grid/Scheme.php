<?php

class Grid_Scheme extends Grid{

	public $head=null;

	function setModel($model,$fields=null){
		parent::setModel($model,$fields);
		
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

		if(!$this->model['ActiveStatus']){
			$this->setTDParam('name','style/color','red');
			$this->setTDParam('name','style/text-decoration','line-through');
		}
		else
			$this->setTDParam('name','style/color','');

		$this->current_row['limits']= 'Min ' .$this->model['MinLimit'] . ' /- ' . ($this->model['MaxLimit'] !=-1? ' to ' . $this->model['MaxLimit'] .' /-': '');
		$this->current_row_html['Interest'] = $this->model['Interest'] . '% <small><small class="atk-text-dimmed">'. $this->model['ReducingOrFlatRate'] .'</small></span>';
		$this->current_row_html['ProcessingFees']= $this->current_row['ProcessingFees'] . ' '. ($this->model['ProcessingFeesinPercent']? ' %': ' /-');

		parent::formatRow();
	}

	function format_PremiumMode($field){
		$this->current_row_html[$field]= $this->current_row['PremiumMode'] . ' ['.$this->current_row['NumberOfPremiums'].']' ;
	}
}