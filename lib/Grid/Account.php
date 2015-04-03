<?php

class Grid_Account extends Grid {
	
	function setModel($model,$fields=null){
		parent::setModel($model,$fields);

		$m_model = $this->model->getElement('member_id')->getModel();
		$m_model->title_field='name';

		// $this->removeColumn('ActiveStatus');

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


		parent::formatRow();
	}

}