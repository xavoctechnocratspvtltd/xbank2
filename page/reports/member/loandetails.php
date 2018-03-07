<?php

class page_reports_member_loandetails extends Page {
	public $title='Member Loan Detail Report';

	function page_index(){
		// parent::init();

		$form = $this->add('Form');
		$type_array = [];
		foreach (explode(",", MEMBER_TYPES) as $key => $value) {
			$type_array[$value] = $value; 
		}

		$form->addField('Dropdown','type')->setValueList(array_merge(["All"=>"All"],$type_array));
		$bank_field = $form->addField('Dropdown','bank')->setEmptyText('All Banks');
		$bank_field->setModel('Bank');
		$form->addField('dropdown','status')->setValueList(array('all'=>'All','0'=>'InActive','1'=>'Active'));
		$form->addField('Line','pan_no');
		$form->addField('Line','adhar_no');
		$form->addSubmit('Get List');
		
		$member_model=$this->add('Model_Member');
		$member_model->setOrder('created_at','desc');
		$member_model->addExpression('bank_a_id')->set($member_model->refSQL('bankbranch_a_id')->fieldQuery('bank_id'));
		$member_model->addExpression('bank_b_id')->set($member_model->refSQL('bankbranch_b_id')->fieldQuery('bank_id'));
		
		$member_model->addExpression('last_loan_date')->set(function($m,$q){
			return $this->add('Model_Account_Loan')
						->addCondition('member_id',$q->getField('id'))
						->setOrder('created_at','desc')
						->setLimit(1)
						->fieldQuery('created_at');
		});

		$member_model->addExpression('last_loan_account')->set(function($m,$q){
			return $this->add('Model_Account_Loan')
						->addCondition('member_id',$q->getField('id'))
						->setOrder('created_at','desc')
						->setLimit(1)
						->fieldQuery('AccountNumber');
		});

		$member_model->addExpression('last_loan_amount')->set(function($m,$q){
			return $this->add('Model_Account_Loan')
						->addCondition('member_id',$q->getField('id'))
						->setOrder('created_at','desc')
						->setLimit(1)
						->fieldQuery('Amount');
		});


		$grid=$this->add('Grid');
		if($_GET['filter']){
			$this->api->stickyGET('filter');
			$this->api->stickyGET('status');
			$this->api->stickyGET('type');
			$this->api->stickyGET('bank');
			$this->api->stickyGET('pan_no');
			$this->api->stickyGET('adhar_no');

			if($_GET['status'] !=='all')
				$member_model->addCondition('is_active',$_GET['status']==0?false:true);
			
			if($_GET['type']){
				$this->api->stickyGET('type');
				if($_GET['type'] != "All"){
					$member_model->addCondition('memebr_type',$_GET['type']);
				}
			}
			if($_GET['bank']){
				$this->api->stickyGET('bank');
				$member_model->addCondition([['bank_a_id',$_GET['bank']],['bank_b_id',$_GET['bank']]]);
			}

			if($_GET['pan_no']){
				$this->app->stickyGET('pan_no');
				$member_model->addCondition('pan_no',$_GET['pan_no']);
			}

			if($_GET['adhar_no']){
				$this->app->stickyGET('adhar_no');
				$member_model->addCondition('AdharNumber',$_GET['adhar_no']);
			}

		}else{
			$member_model->addCondition('id',-1);
		}
		// $grid->add('H3',null,'grid_buttons')->set('Member Repo As On '. date('d-M-Y',strtotime($till_date))); 
		$grid->setModel($member_model,array('member_no','branch','gender','name','FatherName','CurrentAddress','landmark','tehsil','city','PhoneNos','created_at','DOB','last_loan_account','last_loan_date','last_loan_amount','is_active','is_defaulter'));
		$grid->addPaginator(1000);
		$grid->addQuickSearch(array('member_no','name','PhoneNos'));
		$self=$this;

		$self=$this;
		$grid->addColumn('comment');
		$grid->addMethod('format_comment',function($g,$f)use($self){
			// throw new \Exception($g->model->id, 1);
			$comment_model=$self->add('Model_Comment');//->load($g->model->id);
			$comment_model->addCondition('member_id',$g->model->id);
			$comment_model->setOrder('created_at','desc');
			$comment_model->tryLoadAny();
			$narration=$comment_model->get('narration');
			$g->current_row[$f]=$narration;
		});

		$grid->addMethod('init_image2',function($g){
			$this->js('click')->_selector('img')->univ()->frameURL('IMAGE',[$this->app->url('image'),'image_id'=>$this->js()->_selectorThis()->data('sig-image-id') ]);
		});

		$grid->addMethod('format_image2',function($g,$f)use($self){
			$g->current_row_html[$f]=$g->model['doc_thumb_url']?'<img src="'.$g->model['doc_thumb_url'].'" data-sig-image-id="'.$g->model['sig_image_id'].'"/>':'';
		});
		
		$grid->addFormatter('comment','comment');
		$grid->addFormatter('landmark','wrap');
		
		$grid->removeColumn('sig_image_id');

		// $js=array(
		// 	$this->js()->_selector('.mymenu')->parent()->parent()->toggle(),
		// 	$this->js()->_selector('#header')->toggle(),
		// 	$this->js()->_selector('#footer')->toggle(),
		// 	$this->js()->_selector('ul.ui-tabs-nav')->toggle(),
		// 	$this->js()->_selector('.atk-form')->toggle(),
		// 	);

		// $grid->js('click',$js);

		if($form->isSubmitted()){
			$send = array('pan_no'=>$form['pan_no'],'adhar_no'=>$form['adhar_no'],'type'=>$form['type'],'bank'=>$form['bank'],'status'=>$form['status'],'filter'=>1);
			$grid->js()->reload($send)->execute();

		}	
	}

}