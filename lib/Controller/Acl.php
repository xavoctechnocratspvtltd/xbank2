<?php

class Controller_Acl extends AbstractController{
	
	public $default_view = true;
	public $branch_field= 'branch_id';
	public $acl;

	function init(){
		parent::init();

		if($this->api->currentStaff->isSuper()) return;

		$model_class = ($this->owner instanceof CRUD or $this->owner instanceof Grid or $this->owner instanceof Form)?
						$this->owner->model:
							($this->owner instanceof SQL_Model?
								$this->owner:null);

		$view_class=$this->owner;
		if($model_class instanceof SQL_Model){
			$view_class = $this->owner->owner;
		}


		if($view_class instanceof CRUD or $view_class instanceof Grid or $view_class instanceof Form){
			$view_class = $view_class->owner;
		}

		$this->acl = $acl = $this->add('Model_Acl')
					->setStaff($this->api->currentStaff)
					->setModelClass(get_class($model_class?:$view_class));

		$acl->tryLoadAny();

		if(!$acl->loaded()){
			$acl['can_view']=$this->default_view;
			$acl->save();
		}


		if(!$acl->canView() and !$model_class){
			$view_class->add('View_Error')->set('Not Authorized');
			throw $this->exception('','StopInit');
		}

		if(!$acl->canView() and $model_class){
			$model_class->addCondition('id',-1);
		}

		if(!$model_class) return;

		if($acl->isCurrentBranchOnly() and $model_class->hasElement('branch_id')){
			$model_class->addCondition($this->branch_field,$this->api->currentStaff['branch_id']);
		}

		if(!$this->owner instanceof CRUD OR $this->owner->isEditing()) return;
		
		if(!$acl->allowAdd()){
			$this->owner->allow_add=false;
			if($this->owner->add_button)
				$this->owner->add_button->destroy();
		}

		if(!$acl->allowEdit()){
			$this->owner->allow_edit=false;
			$this->owner->grid->removeColumn('edit');
			if($this->owner->grid->hasColumn('JointMember'))
				$this->owner->grid->removeColumn('JointMember');
		}

		if(!$acl->allowDelete()){
			$this->owner->allow_del=false;
			$this->owner->grid->removeColumn('delete');
		}
	}

	function documentACL(){
		$doc_acl = $this->add('Model_DocumentAcl')->setStaff($this->api->currentStaff);
		if(!$doc_acl->count()->getOne()) return;

		$doc_acl_data = [];
		foreach ($doc_acl->getRows() as $data) {
			$doc_acl_data[$data['documents_id']] = $data;
		}

		$this->owner->grid->addMethod('format_editnew',function($g,$f)use($doc_acl_data){
			if(isset($doc_acl_data[$g->model['documents_id']]) && $doc_acl_data[$g->model['documents_id']]['allow_edit']){
				$g->current_row_html[$f] = '<button type="button" class="atk-button-small pb_edit"><i class="icon-pencil"></i>&nbsp;Edit</button>';
			}else
				$g->current_row[$f] = " ";
		});
		$this->owner->grid->addMethod('format_deletenew',function($g,$f)use($doc_acl_data){
			if(isset($doc_acl_data[$g->model['documents_id']]) && $doc_acl_data[$g->model['documents_id']]['allow_del']){
				$g->current_row_html[$f] = '<button class="atk-button-small do-delete  atk-effect-danger atk-delete-button" data-id="194318"><span class="icon-trash"></span>&nbsp;Delete</button>';
			}else
				$g->current_row[$f] = " ";
		});
		$this->owner->grid->addColumn('editnew','edit');
		$this->owner->grid->addColumn('deletenew','delete');
		// $this->owner->add('View')->set(json_encode($doc_acl_data));
	}
}