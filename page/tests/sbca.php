<?php

class page_tests_sbca extends Page {
    public $title= 'Saving And Current Tests';
    public $dir='tests/sb';
    function init(){
        parent::init();
        $dir= $this->dir;

        $m=$this->add('Model_AgileTest',array('dir'=>$this->dir));

        $l = $this->add('Grid');
        $l->setModel($m);
        $l->addTotals()->setTotalsTitle('name', '%s test%s');
        
        $l->addHook('formatRow', function($l)use($dir){
            $n = $l->current_row['name'];
            $n = str_replace('.php', '', $n);
            $n = '<a href="'.$l->api->url($dir.'/'.$n).'">'.$n.'</a>';
            $l->current_row_html['name'] = $n;
        });
    }
}

