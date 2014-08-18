<?php

class page_stock_reports_item extends Page {
	function init(){
		parent::init();

		$form=$this->add('Form');
		$item_field=$form->addField('dropdown','item');
		$item_field->setModel('Stock_Item');
		$form->addSubmit('GET LIST');

		$grid=$this->add('Grid');
		$item=$this->add('Model_Stock_Item');

		if($_GET['item'])
			$item->addCondition('id',$_GET['item']);
		else
			$item->addCondition('id',-1);
		$grid->setModel($item);
		$grid->removeColumn('category');

		if($form->isSubmitted()){
			$grid->js()->reload(array('item'=>$form['item']))->execute();
		}
	}
}
