<?php

namespace wpai_acf_add_on\fields\acf;

/**
 * Class FieldLocationField
 * @package wpai_acf_add_on\fields\acf
 */
class FieldLocationField extends FieldGoogleMap {

    /**
     *  Field type key
     */
    public $type = 'location-field';

    /**
     * @return false|int|mixed|string
     */
    public function getFieldValue() {
        $values = $this->getOption('values');
        return $values['address'][$this->getPostIndex()] . "|" . $values['lat'][$this->getPostIndex()] . "," . $values['lng'][$this->getPostIndex()];
    }
}