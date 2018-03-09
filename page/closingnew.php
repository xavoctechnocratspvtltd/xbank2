<?php

class page_closingnew extends Page {
	
	function init(){
		parent::init();
		ini_set('memory_limit', '2048M');
		set_time_limit(0);
		gc_enable();
		$this->closing_vp = $this->add('VirtualPage');
		$this->closing_vp->set([$this,'performClosing']);
	}

	function page_index(){


		$grid = $this->add('Grid');
		$branch_model= $this->add('Model_Branch')
					->addCondition('published',true)
					->addCondition('PerformClosings',true)
					;
		$branch_model->addExpression('last_closing')->set($branch_model->refSQL('Closing')->setLimit(1)->fieldQuery('daily'));

		$grid->setModel($branch_model,['name','last_closing']);

		$grid->addColumn('Button','perform_closing');

		if($branch_id = $this->app->stickyGET('perform_closing')){
			$this->js()->univ()->frameURL('Closing Of',$this->closing_vp->getURL())->execute();
		}
		
		// if($_GET['do']){
		// 	$this->api->forget('progress_data');
			
		// 	$scheme = null;
		// 	$account = null;
		// 	// ===== uncomment to test for scheme or account below
		// 	// $scheme = $this->add('Model_Scheme')->load(535);
		// 	// $account = $this->add('Model_Account')->load(191841);
		// 	// ======

		// 	try{
		// 		$this->api->db->dsql()->owner->beginTransaction();
		// 		$this->api->closing_running =true;
		// 		foreach ($b=$this->add('Model_Branch')->addCondition('PerformClosings',true) as $branch_array) {
		// 			$b->performClosing($this->api->today,$scheme,$account);
		// 		}
		// 		$this->api->markProgress('COMMITING IN DB',0,'ALL BRANCHES CLOSED',1);
		// 		// throw new \Exception("Error Processing Request", 1);
				
		// 		// $this->api->db->dsql()->owner->rollBack();

		// 		// $p = $this->add('Model_TransactionRow');
		// 		// $p->join('accounts','account_id')->addField('AccountNumber');
		// 		// $p->addCondition('account_id',$account->id);
				
		// 		// echo "<pre>";
		// 		// print_r($p->getRows());
		// 		// echo "</pre>";
		// 		// $grid = $this->add('Grid');
		// 		// $grid->setSource($p->getRows());
		// 		// $grid->addColumn('voucher_no');
		// 		// $grid->addColumn('created_at');
		// 		// $grid->addColumn('Narration');
		// 		// $grid->addColumn('amountDr');
		// 		// $grid->addColumn('amountCr');
		// 		// $grid->addColumn('PaneltyCharged');
		// 		// $grid->addColumn('PaneltyPosted');

		// 		// throw new \Exception("Error Processing Request", 1);
		// 		$this->api->db->dsql()->owner->commit();
		// 	}catch(Exception $e){
		// 		$this->api->db->dsql()->owner->rollBack();
		// 		throw $e;
		// 	}
		// 	unset($this->api->closing_running);
		// }else{
		// 	// $this->api->markProgress('branch',2,'Udaipur branch',5);
		// 	// $this->api->markProgress('schemes',rand(100,1024),'CC 4% Interest',1024);
		// 	// $this->api->markProgress('daily','2014-04-01','Running Daily','2014-05-31');
		// 	$this->add('progressview/View_Progress');
		// }
	}

	function performClosing($page){
		$page->app->stickyGET('perform_closing');
		$v = $page->add('View');
		$v->addClass('progress-base');
		$page->js()->_load('progressview');

		$v->add('View_Console')->set(function($c){
			$this->app->sse_stream = $c;
			$branch_id = $_GET['perform_closing'];

			$scheme = null;
			$account = null;
		// 	// ===== uncomment to test for scheme or account below
			$scheme = $this->add('Model_Scheme')->load(535);
			// $account = $this->add('Model_Account')->load(191841);
		// 	// ======

			try{
				$this->api->db->dsql()->owner->beginTransaction();
				$this->api->closing_running =true;
					$branch = $this->add('Model_Branch')->load($branch_id);
					$branch->performClosing($this->api->today,$scheme,$account);

				$this->api->markProgress('COMMITING IN DB',0,'ALL BRANCHES CLOSED',1);
				throw new \Exception("Error Processing Request", 1);
				
				// $this->api->db->dsql()->owner->rollBack();

				// $p = $this->add('Model_TransactionRow');
				// $p->join('accounts','account_id')->addField('AccountNumber');
				// $p->addCondition('account_id',$account->id);
				
				// echo "<pre>";
				// print_r($p->getRows());
				// echo "</pre>";
				// $grid = $this->add('Grid');
				// $grid->setSource($p->getRows());
				// $grid->addColumn('voucher_no');
				// $grid->addColumn('created_at');
				// $grid->addColumn('Narration');
				// $grid->addColumn('amountDr');
				// $grid->addColumn('amountCr');
				// $grid->addColumn('PaneltyCharged');
				// $grid->addColumn('PaneltyPosted');

				// throw new \Exception("Error Processing Request", 1);
				$this->api->db->dsql()->owner->commit();
			}catch(Exception $e){
				$this->api->db->dsql()->owner->rollBack();
				throw $e;
			}
			unset($this->api->closing_running);
		});
	}
}