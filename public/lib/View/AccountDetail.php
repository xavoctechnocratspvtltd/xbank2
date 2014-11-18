<?php


class View_AccountDetail extends View {
	public $account=null;
	public $title="Account Detail";
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

		if(!$ac_m->loaded()){
			return parent::recursiveRender();
		}
		
		$ac_m_join=$ac_m->join('members','member_id');
		$ac_m_join->addField('memberName','name');
		$ac_m_join->addField('FatherName','FatherName');
		$ac_m_join->addField('PermanentAddress','PermanentAddress');
		$ac_m_join->addField('PhoneNos');

		$guarenters = $ac_m->ref('AccountGuarantor');
		$g_m_join = $guarenters->join('members','member_id');
		$g_m_join->addField('memberName','name');

		$grid= $this->add('Grid',null,'guaranters');
		$grid->setModel($guarenters);

		$this->template->trySet('amount_caption',$ac_m->getElement('Amount')->caption());

		$schemes = $this->add('Model_Scheme');
		$sc_ac_join = $schemes->join('accounts.scheme_id');
		$sc_ac_join->addField('account_id','id');
		$schemes->addCondition('account_id',$ac_m->id);

		$scheme_grid_fields=array();

		$grid= $this->add('Grid',null,'schemes');
		$grid->setModel($schemes,array('name','Interest','AccountOpenningCommission','ProcessingFees','PremiumMode','MaturityPeriod','NumberOfPremiums','SchemeType','SchemeGroup','ReducingOrFlatRate'));

		$documents = $ac_m->ref('DocumentSubmitted');
		$grid= $this->add('Grid',null,'documents');
		$grid->setModel($documents);

		if(($premium_count =  $ac_m->ref('Premium')->count()->getOne()) > 0){
			$premium_amount = $ac_m->ref('Premium')->fieldQuery('Amount')->getOne();
			$this->template->trySet('emidetails',$premium_count . ' x ' .$premium_amount);
		}

		$premium_grid_field = array('DueDate','Amount','Paid','PaidOn','AgentCommissionSend','AgentCommissionPercentage','PaneltyCharged','PaneltyPosted');
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