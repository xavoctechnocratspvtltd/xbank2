<?php

class Grid_Account extends Grid {
	
	function setModel($model,$fields=null){
		parent::setModel($model,$fields);


		$m_model = $this->model->getElement('member_id')->getModel();
		$m_model->title_field='member_name';

		$this->addFormatter('scheme','wrap');

		$this->removeColumn('ActiveStatus');
		$this->removeColumn('ModeOfOperation');
		$this->removeColumn('LoanInsurranceDate');

		$this->addPaginator(50);
	}

	function formatRow(){

		if(!$this->model['ActiveStatus']){
			if($this->hasColumn('MaturedStatus') and !$this->model['MaturedStatus']){
				$this->setTDParam('AccountNumber','style/color','green');
			}else{
				$this->setTDParam('AccountNumber','style/color','red');
			}
		}else{
			$this->setTDParam('AccountNumber','style/color','');
		}

		$member_exploded = explode("::",$this->current_row['member']);
		$member_name  = $member_exploded[0]. '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'.$member_exploded[1].'</small></span>';
		$this->current_row_html['member']=$member_name;

		parent::formatRow();
	}
}