<?php

class page_documents_manager extends Page {
	function init(){
		parent::init();


		switch ($_GET['what']) {
			case 'MemberDocuments':
				$model='Member'; //Not used
				$uid= 'members_id'; // table_id from url
				$doc_sub_field='member_id'; // Field in documentsubmitted
				break;
			case 'AgentDocuments':
				$model='Agent';
				$uid= 'agents_id';
				$doc_sub_field='agent_id';
				break;
			case 'AgentGuarantor':
				$model='AgentGuarantor';
				$uid= 'agent_guarantors_id';
				$doc_sub_field='agentguarantor_id';
				break;
			case 'DSADocuments':
				$model='DSA';
				$uid= 'dsa_id';
				$doc_sub_field='dsa_id';
				break;
			case 'DSAGuarantor':
				$model='DSAGuarantor';
				$uid= 'dsa_guarantors_id';
				$doc_sub_field='dsaguarantor_id';
				break;
			case 'LoanAccount':
			case 'SavingAccount':
			case 'FixedMISAccount':
			case 'RDandDDSAccount':
			case 'CCAccount':
			case 'OtherAccounts':
				$model='Account';
				$uid= 'accounts_id';
				$doc_sub_field='accounts_id';
				break;
			
			default:
				# code...
				break;
		}
		$this->api->stickyGET('what');
		$this->api->stickyGET($uid);

		$_m = $this->add('Model_DocumentSubmitted');
		$_m->addCondition($doc_sub_field,$_GET[$uid]);

		$crud = $this->add('CRUD');


		$crud->setModel($_m,array('documents_id','Description','doc_image_id'),array('documents','Description','doc_image','submitted_on'));
		$acl = $crud->add('Controller_Acl');
		$acl->documentACL();

		if($crud->form){
			$crud->form->getElement('documents_id')->getModel()->addCondition($_GET['what'],true);
		}

	}
}