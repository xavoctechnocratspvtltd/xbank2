<?php


class View_AccountDetail extends View {
	public $account=null;
	public $title="Account Detail";

	public $show_pan_adhaar=true;

	function init(){
		parent::init();
		// $this->add('H2')->set("Account Detail");
		if($this->account==null)
			throw new Exception("AccountDetail View Must Have Loaded Account Model defined", 1);
		if(!($this->account instanceof  Model_Account))
			throw new Exception("AccountDetail View Must Have Loaded Account Model defined", 1);
	}

	function recursiveRender(){
		$ac_m=$this->account;
		
		$ac_m->addExpression('maturity_date')->set(function($m,$q){
			return "(IF (".$q->getField('account_type')."='FD' OR ".$q->getField('account_type')."='MIS',(
					DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod + 0) DAY)
				),(
				DATE_ADD(DATE_ADD(DATE(".$q->getField('created_at')."), INTERVAL +(".$m->scheme_join->table_alias.".MaturityPeriod) MONTH), INTERVAL + 0 DAY)
				)
				)
				)";
		});

		if(!$ac_m->loaded()){
			return parent::recursiveRender();
		}
		// throw new \Exception($ac_m['member_id'], 1);
		$ac_m_join=$ac_m->join('members','member_id');
		// $ac_m_join->addField('MemberID','id');
		$ac_m_join->addField('memberName','name');
		$ac_m_join->addField('member_no');
		$ac_m_join->addField('FatherName','FatherName');
		// $ac_m_join->addField('accountopeningdate','created_at');
		$ac_m_join->addField('PermanentAddress','PermanentAddress');
		$ac_m_join->addField('PhoneNos');

		// insurance info
		$ac_m->addExpression('mem_insurance_start_date')->set(function($m,$q){
			$mi = $m->add('Model_MemberInsurance',['table_alias'=>'minsd']);
			$mi->addCondition('accounts_id',$m->getElement('id'));
			$mi->setOrder('id','desc');
			$mi->setLimit(1);
			return $q->expr('IFNULL([0],"-")',[$mi->fieldQuery('insurance_start_date')]);
		});
		$ac_m->addExpression('mem_next_insurance_due_date')->set(function($m,$q){
			$mi = $m->add('Model_MemberInsurance',['table_alias'=>'mindd']);
			$mi->addCondition('accounts_id',$m->getElement('id'));
			$mi->setOrder('id','desc');
			$mi->setLimit(1);
			return $q->expr('if([0],DATE([0]),DATE([1]))',[$mi->fieldQuery('next_insurance_due_date'),$m->getElement('created_at')]);
		});
		$ac_m->addExpression('insurance_narration')->set(function($m,$q){
			$mi = $m->add('Model_MemberInsurance',['table_alias'=>'mindd']);
			$mi->addCondition('accounts_id',$m->getElement('id'));
			$mi->setOrder('id','desc');
			$mi->setLimit(1);
			return $mi->fieldQuery('narration');
		});

		// end of insurance info
		if($this->show_pan_adhaar){
			$ac_m_join->addField('PanNo');
			$ac_m_join->addField('AdharNumber');
		}

		$ac_m->addExpression('member_sm_account')->set(function($m,$q){
			return  $this->add('Model_Account_SM',['table_alias'=>'sm_accounts'])->addCondition('member_id',$q->getField('member_id'))->setLimit(1)->fieldQuery('AccountNumber');
		});

		$guarenters = $ac_m->ref('AccountGuarantor');
		$g_m_join = $guarenters->join('members','member_id');
		// $g_m_join->addField('guarantor_name','name');
		$g_m_join->addField('guarantor_FatherName','FatherName');
		$g_m_join->addField('guarantor_Mobile_no','PhoneNos');
		// $g_m_join->addField('MemberID','id');

		$grid= $this->add('Grid',null,'guaranters');
		$grid->setModel($guarenters);
		$grid->removeColumn('account');
		$grid->addOrder()->move('created_at','last')->now();

		$this->template->trySet('id',$ac_m['member_id']);
		$this->template->trySet('amount_caption',$ac_m->getElement('Amount')->caption());

		$schemes = $this->add('Model_Scheme');
		$sc_ac_join = $schemes->join('accounts.scheme_id');
		$sc_ac_join->addField('account_id','id');
		$schemes->addCondition('account_id',$ac_m->id);

		$scheme_grid_fields=array();

		$grid= $this->add('Grid',null,'schemes');
		$grid->setModel($schemes,array('name','Interest','AccountOpenningCommission','ProcessingFees','PremiumMode','MaturityPeriod','NumberOfPremiums','SchemeType','type','SchemeGroup','ReducingOrFlatRate'));

		$documents = $ac_m->ref('DocumentSubmitted');
		$grid_document= $this->add('Grid',null,'documents');
		$grid_document->setModel($documents);

		$grid_document->addFormatter('Description','wrap');
		
		$grid_document->addMethod('format_dealer',function($g,$f)use($ac_m){
			if(!$g->model['dealer_id'])
				$g->current_row[$f] = $ac_m['dealer'];
		});
		$grid_document->addColumn('dealer','dealer');

		$grid_document->addMethod('format_agent',function($g,$f)use($ac_m){
			if(!$g->model['agent_id'])
				$g->current_row[$f] = $ac_m['agent'];
		});
		$grid_document->addColumn('agent','agent');
		$grid_document->addMethod('format_doc_image',function($g,$f){
			// if($g->model['doc_image']){
			$g->current_row_html[$f] = '<a width="50px;" href="'.$g->model['doc_image'].'" target="_blank"><img style="width:50px;" src="'.$g->model['doc_image'].'"/></a>';
			// }
		});
		$grid_document->addColumn('doc_image','doc_image');

		$grid_document->removeColumn('member');


		if(($premium_count =  $ac_m->ref('Premium')->count()->getOne()) > 0){
			$premium_amount = $ac_m->ref('Premium')->fieldQuery('Amount')->getOne();
			$this->template->trySet('emidetails',$premium_count . ' x ' .$premium_amount);
		}

		$premium_grid_field = array('DueDate','Amount','Paid','PaidOn','AgentCommissionSend','AgentCommissionPercentage','AgentCollectionChargesPercentage','AgentCollectionChargesSend','PaneltyCharged','PaneltyPosted');
		$premium = $ac_m->ref('Premium');
		$grid= $this->add('Grid_AccountsBase',null,'premiumdetail');
		$grid->setModel($premium,$premium_grid_field);
		$grid->addSno();

		$ac_m->reload();
		$this->setModel($ac_m);
		// $this->template->trySet('memberName',$ac_m)

		parent::recursiveRender();
	}

	function defaultTemplate(){
		return array('view/accountdetail');
	}

}