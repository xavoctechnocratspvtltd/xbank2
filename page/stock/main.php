<?php

class page_stock_main extends Page {

	public $title= 'Stock Manager';
	
	function init(){
		parent::init();
		$this->add('Controller_Acl',array('default_view'=>false));

		$tabs=$this->add('Tabs');
		$tab1=$tabs->addTabURL('stock_master','Master');
		$tab1=$tabs->addTabURL('stock_actions','Transaction Actions');
		$tab1=$tabs->addTabURL('stock_ledger_main','Ledgers');
		$tab1=$tabs->addTabURL('stock_reports_main','Reports');

		//Creating Default Container and Row
		$branch = $this->add('Model_Branch');
		foreach ($branch as $junk) {
			$container = $this->add('Model_Stock_Container');
			$row = $this->add('Model_Stock_Row');
			$container->_dsql()->del('where');
			$row->_dsql()->del('where');
			
			$container->addCondition('branch_id',$junk['id']);
			$container->addCondition('name','General');
			$container->tryLoadAny();
				if(!$container->loaded()){
					$container['name'] = "General";
					$container['branch_id'] = $junk['id'];
					$container->save();

					$row->addCondition('branch_id',$junk['id']);
					$row->addCondition('name','General');
					$row->tryLoadAny();
					if(!$row->loaded()){
						$row['name']="General";
						$row['branch_id']=$junk['id'];
						$row['container_id']=$container['id'];
						$row->save();
					}
				}
		}
		foreach ($branch as $junk) {
			$container = $this->add('Model_Stock_Container');
			$row = $this->add('Model_Stock_Row');
			$container->_dsql()->del('where');
			$row->_dsql()->del('where');
			
			$container->addCondition('branch_id',$junk['id']);
			$container->addCondition('name','Dead');
			$container->tryLoadAny();
				if(!$container->loaded()){
					$container['name'] = "Dead";
					$container['branch_id'] = $junk['id'];
					$container->save();

					$row->addCondition('branch_id',$junk['id']);
					$row->addCondition('name','Dead');
					$row->tryLoadAny();
					if(!$row->loaded()){
						$row['name']="Dead";
						$row['branch_id']=$junk['id'];
						$row['container_id']=$container['id'];
						$row->save();
					}
				}
		}	
		
		foreach ($branch as $junk) {
			$container = $this->add('Model_Stock_Container');
			$row = $this->add('Model_Stock_Row');
			$container->_dsql()->del('where');
			$row->_dsql()->del('where');
			
			$container->addCondition('branch_id',$junk['id']);
			$container->addCondition('name','UsedDefault');
			$container->tryLoadAny();
				if(!$container->loaded()){
					$container['name'] = "UsedDefault";
					$container['branch_id'] = $junk['id'];
					$container->save();

					$row->addCondition('branch_id',$junk['id']);
					$row->addCondition('name','UsedDefault');
					$row->tryLoadAny();
					if(!$row->loaded()){
						$row['name']="UsedDefault";
						$row['branch_id']=$junk['id'];
						$row['container_id']=$container['id'];
						$row->save();
					}
				}
		}
	}
}