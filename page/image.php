<?php

class page_image extends Page {
	function init(){
		parent::init();
		$m = $this->add('filestore/Model_File')->tryLoad($_GET['image_id']);
		echo '<img src="'.$m['url'].'"/>';
	}
}