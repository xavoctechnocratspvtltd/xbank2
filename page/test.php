<?php



class page_test  extends Page {
	
	function init(){
		parent::init();

		// throw new \Exception("Must kept thrown in commit version, comment on server and then stash when done", 1);
		

		ini_set('memory_limit', '2048M');
		set_time_limit(0);

		$this->vp = $this->add('VirtualPage');

		$account = $this->add('Model_Account_SM');
		$account->addExpression('tra_cr')->set(function($m,$q){
			return $this->add('Model_TransactionRow')
						->addCondition('account_id',$q->getField('id'))
						->addCondition('amountCr','>',0)
						->sum('amountCr')
						;
		});

		$account->addExpression('tra_dr')->set(function($m,$q){
			return $this->add('Model_TransactionRow')
						->addCondition('account_id',$q->getField('id'))
						->addCondition('amountDr','>',0)
						->sum('amountDr')
						;
		});

		$account->addExpression('total_cr')->set(function($m,$q){
			return $q->expr('(IFNULL([0],0)+IFNULL([1],0))-(IFNULL([2],0)+IFNULL([3],0))',[
				$q->getField('OpeningBalanceCr'),
				$m->getElement('tra_cr'),
				$q->getField('OpeningBalanceDr'),
				$m->getElement('tra_dr')
			]);
		})->sortable(true);

		$account->addExpression('mod_100')->set(function($m,$q){
			return $q->expr('MOD([0],100)',[$m->getElement('total_cr')]);
		});

		$account->addExpression('existing_share_count')->set(function($m,$q){
			return $m->add('Model_Share')
					->addCondition('current_member_id',$q->getField('member_id'))
					->count();
		});

		$account->setOrder('created_at','asc');

		// $account->addCondition('mod_100','<>',0);
		$account->addCondition('total_cr','>',0);

		// $grid = $this->add('Grid');
		// $grid->setModel($account,['AccountNumber','member_name_only','OpeningBalanceDr','OpeningBalanceCr','tra_cr','tra_dr','total_cr','mod_100']);

		// $grid->addPaginator(200);

		$btn = $this->add('Button')->set("Allot Share");
		$btn->on('click',$this->js()->univ()->frameURL($this->vp->getURL()));
		
		$safe_app_now = $this->app->now;

		$this->vp->set(function($page)use($account){
			$page->add('View_Console')->set(function($c)use($account){
				$account->setActualFields(['AccountNumber','total_cr','existing_share_count','created_at','member_id']);
				$total = $account->count()->getOne();
				$per=0;

				$i=0;

				$c->out('Total '. $total.' accounts');
				foreach ($account as $acc) {
					$no_of_shares = (int) $acc['total_cr']/100;
					if($acc['existing_share_count'] >= $no_of_shares) {
						$c->err($acc['AccountNumber'].' is already having shares');
						continue;
					}
					$sm = $this->add('Model_Share');
					$this->app->now = $acc['created_at'];
					$sm->createNew($no_of_shares,$acc['member_id']);
					
					if( ((int)($i/$total*100)) > $per ){
						$per = (int)($i/$total*100);
						$c->out($per.'% Done');
						usleep(500);
					}

					$sm->destroy();
					$i++;
				}
			});
		});

	}

}
