<?php

class View_BSPLChunks_SchemeGroup extends View {
	public $SchemeGroup=null;

	function init(){
		parent::init();
		
		if(!$this->SchemeGroup)
			throw $this->exception('Scheme Gorup is must');

		
	}
}