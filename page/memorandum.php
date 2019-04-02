<?php

class page_memorandum extends Page {
	public $title='Memorandum';

	function init(){
		parent::init();

		$tab = $this->add('Tabs');
		$tab->addTabUrl('memorandum_entry','Create Memorandum');
	}
}