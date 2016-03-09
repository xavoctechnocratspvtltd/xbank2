<?php

class Grid_Account extends Grid {
	
	function setModel($model,$fields=null){
		parent::setModel($model,$fields);


		$m_model = $this->model->getElement('member_id')->getModel();
		$m_model->title_field='member_name';

		$this->addFormatter('member','wrap');

		$this->addFormatter('scheme','wrap');
		if($this->hasColumn('LoanAgainstAccount'))
			$this->addFormatter('LoanAgainstAccount','lg');

		$this->removeColumn('ActiveStatus');
		$this->removeColumn('ModeOfOperation');
		$this->removeColumn('LoanInsurranceDate');
		$this->removeColumn('NomineeAge');
		$this->removeColumn('RelationWithNominee');


		$this->addPaginator(500);
	}

	function formatRow(){

		if(!$this->model['ActiveStatus']){
			if($this->hasColumn('MaturedStatus') and $this->model['MaturedStatus']){
				$this->setTDParam('AccountNumber','style/color','green');
			}else{
				$this->setTDParam('AccountNumber','style/color','red');
				$this->setTDParam('name','style/text-decoration','line-through');
			}
		}else{
			$this->setTDParam('AccountNumber','style/color','');
		}

		$member_exploded = explode("::",$this->current_row['member']);
		$member_name  = $member_exploded[0]. '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'.$member_exploded[1].'</small></span>';
		$this->current_row_html['member']=$member_name;

		$agent_exploded = explode("::",$this->current_row['agent']);
		$agent_name  = $agent_exploded[0]. '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'.$agent_exploded[1].'</small></span>';
		$this->current_row_html['agent']=$agent_name;

		$this->current_row_html['Nominee'] = $this->current_row['Nominee'] . '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'.$this->model['RelationWithNominee']. ' - '. $this->model['NomineeAge'] .' Yrs</small></span>';

		parent::formatRow();
	}

	function format_lg($field){
		$ag_acc= $this->model->ref('LoanAgainstAccount_id');
		$ag_acc->getElement('member_id')->getModel()->title_field ='name';

		$this->current_row_html[$field] = $ag_acc['AccountNumber'] . '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'.$ag_acc['member']. '</small></span>';

		if($this->model->isActive() and !$ag_acc->isLocked()){
			$this->setTDParam($field,'style/color','red');
		}else{
			$this->setTDParam($field,'style/color','');
		}
	}
}