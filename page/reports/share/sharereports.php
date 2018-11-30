<?php



class page_reports_share_sharereports extends Page {
	public $title = "Share Reports";

	function init(){
		parent::init();

		$tabs=$this->add('Tabs')->addClass('noneprintalbe-ul');
		$share_list_tab = $tabs->addTab('Share List');

		$g = $share_list_tab->add('Grid');
		$g->setModel('Share')->setOrder('current_member_id');
		$g->addPaginator(200);

		$g->add('VirtualPage')->addColumn('share_history','Share History')->set(function($p){
			$grid = $p->add('Grid');
			$grid->setModel('ShareHistory')
					->addCondition('share_id',$p->id);
		});

		$g->addQuickSearch(['no','current_member']);
		// $tab1=$tabs->addTabURL('reports_share_sharelist','Current Share List');
	}
}