<?php

class page_alltests extends Page {
    function init(){
        parent::init();
        $l = $this->add('Grid');
        $l->setModel('AgileTest');
        $l->addTotals()->setTotalsTitle('name', '%s test%s');
        
        $l->addHook('formatRow', function($l){
            $n = $l->current_row['name'];
            $n = str_replace('.php', '', $n);
            $n = '<a href="'.$l->api->url('tests/'.$n).'">'.$n.'</a>';
            $l->current_row_html['name'] = $n;
        });
    }
}

