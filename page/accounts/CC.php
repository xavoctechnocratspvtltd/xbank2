<?php

class page_accounts_CC extends Page {
	function init(){
		parent::init();

		$crud=$this->add('xCRUD');
		
		
		$crud->addHook('myupdate',function($crud,$form){
			$form->js()->univ()->errorMessage($form['aaa'])->execute();
		});

		if($crud->isEditing("add")){
		    $o=$crud->form->add('Order');
			$k = 1;
			$documents=$this->add('Model_Document');
			$documents->addCondition('CCAccount',true);
			foreach ($documents as $d) {
			    $f=$crud->form->addField('checkbox',$documents['name']);
			   	$o->move($f,'last');
			    $f=$crud->form->addField('line',$documents['name'].' '.$documents['Discription']);
			   	$o->move($f,'last');
			    $k++;
			}

		}

		$crud->setModel('Account_CC',array('AccountNumber','AccountDisplayName','member_id','scheme_id','Amount','agent_id','ActiveStatus'));

		
		if($crud->grid)
			$crud->grid->addPaginator(10);

		if($crud->isEditing('add')){
			$o->now();
		}

	}
}