<?php
class Controller_Validator extends Controller_Validator_Advanced {

    function rule_unique($a,$field){
        /*

        // TODO: why is this not working?

        if(!$this->owner instanceof Model)
            throw $this->exception('Use with Model only');
         */

        $table = $this->owner->table;

        $q = $this->api->db->dsql();

        $result = $q->table($table)
                ->where($field, $a)
                ->where('id','<>', $this->owner->id)
                ->field($field)
                ->getOne();

        if($result !== null) return $this->fail('Value "{{arg1}}" already exists', $a);
    }

}