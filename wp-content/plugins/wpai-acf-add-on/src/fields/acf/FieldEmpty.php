<?php

namespace wpai_acf_add_on\fields\acf;

use wpai_acf_add_on\fields\Field;

/**
 * Class FieldEmpty
 * @package wpai_acf_add_on\fields\acf
 */
class FieldEmpty extends Field {

    /**
     *  Field type key
     */
    public $type = 'empty';

    /**
     * @param $importData
     * @param array $args
     * @return false
     */
    public function import($importData, $args = array()) {
        return FALSE;
    }


}