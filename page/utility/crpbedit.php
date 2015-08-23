<?php
class page_utility_crpbedit extends Page{
	function init(){
		parent::init();

		$g = $this->add('Grid');
		$g->setmodel('Agent',array('code','name','current_individual_crpb'));
		$g->addPaginator(100);

		$g->addFormatter('current_individual_crpb','grid/inline');

		$g->addQuickSearch(array('name'));

	}
}