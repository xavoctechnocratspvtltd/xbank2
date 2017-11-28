<?php

class Grid_Member extends Grid {		
	function setModel($model,$fields=null){
		parent::setModel($model,$fields);
		// $m_model = $this->model->getElement('member_id')->getModel();
		// $m_model->title_field='member_name';

		$this->addFormatter('name','wrap');
		$this->addFormatter('CurrentAddress','wrap');
		$this->addFormatter('Witness1Name','wrap');
		$this->addFormatter('Witness2Name','wrap');
		// $this->addFormatter('ParentName','wrap');
		// $this->addFormatter('Nominee','wrap');
		$this->addFormatter('created_at','wrap');
		// $this->addFormatter('MinorDOB','wrap');
		// $this->addFormatter('doc_image','wrap');
		// $this->addFormatter('PanNo','wrap');
		$this->removeColumn('id');
		$this->removeColumn('PermanentAddress');
		$this->removeColumn('title');
		$this->removeColumn('member_name');
		$this->removeColumn('FatherName');
		$this->removeColumn('Witness1FatherName');
		$this->removeColumn('Witness1Address');
		$this->removeColumn('Witness2FatherName');
		$this->removeColumn('Witness2Address');
		$this->removeColumn('RelationWithParent');
		$this->removeColumn('ParentAddress');
		$this->removeColumn('RelationWithNominee');
		$this->removeColumn('NomineeAge');
		$this->removeColumn('search_string');
		// $this->removeColumn('landmark');
		$this->removeColumn('tehsil');
		$this->removeColumn('city');
		$this->removeColumn('state');
		$this->removeColumn('Cast');
		$this->removeColumn('pin_code');
		$this->removeColumn('district');
		$this->removeColumn('PhoneNos');
		$this->removeColumn('updated_at');
		$this->removeColumn('FilledForm60');
		$this->removeColumn('IsMinor');
		// $this->removeColumn('doc_thumb_url');
		$this->addPaginator(50);
		
	}

	function formatRow(){
		$this->current_row_html['name']=$this->model['title'] ." ".$this->model['name']. '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'."Cast.".$this->model['Cast']."<br>"."Phone No.:"."[  ".$this->model['PhoneNos']."  ] <br>"."Father/Husband Name"."[".$this->model['FatherName']."] <br>".$this->model['PermanentAddress'];
		$this->current_row_html['CurrentAddress']=$this->model['CurrentAddress']. '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'."Landmark "." [" .$this->model['landmark'] ." ] <br>"."Tehsil "." [" .$this->model['tehsil'] ." ] <br>"."District "." [" .$this->model['district'] ." ] <br>"."Pin-code "." [" .$this->model['pin_code'] ." ] <br>"."City "." [" .$this->model['city'] ." ] <br>"."State "." [" .$this->model['state'] ." ] <br>"
			;
		$this->current_row_html['Witness1Name']=$this->model['Witness1Name'] . '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'. " [ Farther Name ::".$this->model['Witness1FatherName' ]."] <br>"."Addess.:" .$this->model['Witness1Address'];
		$this->current_row_html['Witness2Name']=$this->model['Witness2Name'] . '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'. " [ Farther Name ::".$this->model['Witness2FatherName' ]."] <br>"."Addess.:" .$this->model['Witness2Address'];
		$this->current_row_html['ParentName']=$this->model['ParentName'] . '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'. " [".$this->model['RelationWithParent' ]."] <br>".$this->model['ParentAddress'];
		$this->current_row_html['Nominee']=$this->model['Nominee'] . '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'. " [".$this->model['RelationWithNominee' ]."] <br>"."Age -".$this->model['NomineeAge'];
		$this->current_row_html['MinorDOB']=$this->model['IsMinor']?"IsMinor.:".$this->model['IsMinor']."," : '<br/><span class="atk-text-dimmed"><small style="font-size:70%">'. "[ " .$this->model['MinorDOB']. "  ] ";
		
		$this->current_row_html['PanNo']=$this->model['PanNo']?"PAN No.:".$this->model['PanNo'].",":'<br/><span class="atk-text-dimmed"><small style="font-size:70%">'. "FilledForm 60/61 "."[".$this->model['FilledForm60' ]."]";
		$this->current_row_html['doc_thumb_url']=$this->model['doc_thumb_url']?'<img src="'.$this->model['doc_thumb_url'].'"  data-sig-image-id="'.$this->model['sig_image_id'].'"/>':'';
		parent::formatRow();
	}

	function recursiveRender(){
		$this->js('click')->_selector('img')->univ()->frameURL('IMAGE',[$this->app->url('image'),'image_id'=>$this->js()->_selectorThis()->data('sig-image-id') ]);
		return parent::recursiveRender();
	}
}