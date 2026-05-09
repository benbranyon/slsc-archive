<?php
if (!empty($fields)){
    /** @var \pmai_acf_add_on\fields\Field $subField */
    foreach ($fields as $subField){
        $subField->setFieldInputName($field_name . '[' . $field['key'] . ']');
        $subField->view();
    }
}